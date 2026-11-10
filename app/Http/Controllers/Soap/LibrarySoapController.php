<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LibrarySoapController extends Controller
{
    private const NS = 'http://sipus.del.ac.id/soap/library';

    /**
     * GET /soap/sipus.wsdl — kontrak formal layanan (pengganti dokumentasi API di dunia SOAP).
     */
    public function wsdl(): Response
    {
        $endpointUrl = url('/soap/sipus');
        $ns = self::NS;

        $wsdl = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="LibrarySoapService"
    targetNamespace="{$ns}"
    xmlns:tns="{$ns}"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns="http://schemas.xmlsoap.org/wsdl/">

    <!-- 1. Message: parameter input & output -->
    <message name="CheckClearanceStatusRequest">
        <part name="nim_peminjam" type="xsd:string"/>
    </message>
    <message name="CheckClearanceStatusResponse">
        <part name="nim_peminjam" type="xsd:string"/>
        <part name="active_loans" type="xsd:int"/>
        <part name="is_cleared" type="xsd:boolean"/>
    </message>

    <!-- 2. PortType: daftar operasi -->
    <portType name="LibraryPortType">
        <operation name="CheckClearanceStatus">
            <input message="tns:CheckClearanceStatusRequest"/>
            <output message="tns:CheckClearanceStatusResponse"/>
        </operation>
    </portType>

    <!-- 3. Binding: SOAP over HTTP -->
    <binding name="LibrarySoapBinding" type="tns:LibraryPortType">
        <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="CheckClearanceStatus">
            <soap:operation soapAction="{$ns}#CheckClearanceStatus"/>
            <input><soap:body use="literal" namespace="{$ns}"/></input>
            <output><soap:body use="literal" namespace="{$ns}"/></output>
        </operation>
    </binding>

    <!-- 4. Service: alamat endpoint -->
    <service name="LibrarySoapService">
        <port name="LibraryPort" binding="tns:LibrarySoapBinding">
            <soap:address location="{$endpointUrl}"/>
        </port>
    </service>
</definitions>
XML;

        return response($wsdl, 200)->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * POST /soap/sipus — memproses <soap:Envelope> operasi CheckClearanceStatus.
     */
    public function handle(Request $request): Response
    {
        $rawXml = trim($request->getContent());

        if ($rawXml === '') {
            return $this->soapFault('soap:Client', 'Body XML SOAP tidak boleh kosong.');
        }

        // LIBXML_NONET: larang akses jaringan saat parsing (mencegah XXE/SSRF).
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($rawXml, 'SimpleXMLElement', LIBXML_NONET);

        if ($xml === false) {
            libxml_clear_errors();
            return $this->soapFault('soap:Client', 'Format XML tidak valid (Malformed XML).');
        }

        // XPath bebas-namespace: tidak bergantung pada prefix yang dipakai klien.
        $opNodes = $xml->xpath('//*[local-name()="CheckClearanceStatusRequest"]');
        if (empty($opNodes)) {
            return $this->soapFault(
                'soap:Client',
                'Operasi tidak dikenali. Gunakan elemen CheckClearanceStatusRequest di dalam soap:Body.'
            );
        }

        $nimNodes = $xml->xpath('//*[local-name()="CheckClearanceStatusRequest"]/*[local-name()="nim_peminjam"]');
        $nim = empty($nimNodes) ? '' : strtoupper(trim((string) $nimNodes[0]));

        if ($nim === '') {
            return $this->soapFault('soap:Client', 'Elemen <nim_peminjam> kosong atau tidak ditemukan.');
        }

        // Data nyata dari tabel peminjamans (NIM disimpan uppercase di PeminjamanController).
        $activeLoans = Peminjaman::where('nim_peminjam', $nim)
            ->where('status', 'dipinjam')
            ->count();

        $isCleared = $activeLoans === 0 ? 'true' : 'false';
        $safeNim = htmlspecialchars($nim, ENT_XML1, 'UTF-8');
        $ns = self::NS;

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="{$ns}">
    <soap:Header>
        <m:ProviderInfo>SIPUS-Del SOAP Engine v1.0</m:ProviderInfo>
    </soap:Header>
    <soap:Body>
        <m:CheckClearanceStatusResponse>
            <m:nim_peminjam>{$safeNim}</m:nim_peminjam>
            <m:active_loans>{$activeLoans}</m:active_loans>
            <m:is_cleared>{$isCleared}</m:is_cleared>
        </m:CheckClearanceStatusResponse>
    </soap:Body>
</soap:Envelope>
XML;

        return response($responseXml, 200)->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * Pembentuk <soap:Fault> standar (SOAP 1.1 memakai HTTP 500 untuk Fault).
     */
    private function soapFault(string $faultCode, string $faultMessage): Response
    {
        $safeMsg = htmlspecialchars($faultMessage, ENT_XML1, 'UTF-8');

        $faultXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <soap:Fault>
            <faultcode>{$faultCode}</faultcode>
            <faultstring>{$safeMsg}</faultstring>
        </soap:Fault>
    </soap:Body>
</soap:Envelope>
XML;

        return response($faultXml, 500)->header('Content-Type', 'text/xml; charset=utf-8');
    }
}