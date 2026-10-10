<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = Kategori::all()->keyBy('kode_kategori');

        $bukus = [
            // Fiksi
            [
                'isbn' => '9780062315007',
                'judul' => 'To Kill a Mockingbird',
                'penulis' => 'Harper Lee',
                'penerbit' => 'HarperCollins Press',
                'tahun_terbit' => 1960,
                'kategori_id' => $kategoris['KAT-FIK']->id,
                'stok' => 15,
                'sinopsis' => 'Novel klasik Amerika tentang ketidakadilan rasial dan kehilangan innocence di Amerika Selatan masa Great Depression.',
            ],
            [
                'isbn' => '9780141439518',
                'judul' => 'Pride and Prejudice',
                'penulis' => 'Jane Austen',
                'penerbit' => 'Penguin Classics Press',
                'tahun_terbit' => 1813,
                'kategori_id' => $kategoris['KAT-FIK']->id,
                'stok' => 12,
                'sinopsis' => 'Kisah cinta antara Elizabeth Bennet dan Mr. Darcy di Inggris abad ke-19 yang penuh dengan humor sosial.',
            ],
            [
                'isbn' => '9780743273565',
                'judul' => 'The Great Gatsby',
                'penulis' => 'F. Scott Fitzgerald',
                'penerbit' => 'Scribner Press',
                'tahun_terbit' => 1925,
                'kategori_id' => $kategoris['KAT-FIK']->id,
                'stok' => 10,
                'sinopsis' => 'Tragedi tentang ambisi, cinta, dan American Dream yang dilatari era Jazz Age tahun 1920-an.',
            ],
            [
                'isbn' => '9780547928227',
                'judul' => 'The Hobbit',
                'penulis' => 'J.R.R. Tolkien',
                'penerbit' => 'Houghton Mifflin Press',
                'tahun_terbit' => 1937,
                'kategori_id' => $kategoris['KAT-FIK']->id,
                'stok' => 20,
                'sinopsis' => 'Petualangan Bilbo Baggins, seorang hobbit yang diajak dalam ekspedisi berbahaya oleh seorang penyihir.',
            ],

            // Non-Fiksi
            [
                'isbn' => '9780385490818',
                'judul' => 'A Brief History of Time',
                'penulis' => 'Stephen Hawking',
                'penerbit' => 'Bantam Books Press',
                'tahun_terbit' => 1988,
                'kategori_id' => $kategoris['KAT-NON']->id,
                'stok' => 8,
                'sinopsis' => 'Eksplorasi ilmiah tentang asal-usul alam semesta, lubang hitam, dan sifat waktu oleh fisikawan legendaris.',
            ],
            [
                'isbn' => '9780307474278',
                'judul' => 'Outliers: The Story of Success',
                'penulis' => 'Malcolm Gladwell',
                'penerbit' => 'Little, Brown Press',
                'tahun_terbit' => 2008,
                'kategori_id' => $kategoris['KAT-NON']->id,
                'stok' => 14,
                'sinopsis' => 'Investigasi faktor-faktor tersembunyi di balik kesuksesan luar biasa, termasuk aturan 10.000 jam.',
            ],

            // Sains & Teknologi
            [
                'isbn' => '9780132350884',
                'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall Press',
                'tahun_terbit' => 2008,
                'kategori_id' => $kategoris['KAT-SAI']->id,
                'stok' => 25,
                'sinopsis' => 'Panduan menulis kode yang bersih, mudah dibaca, dan mudah dipelihara oleh Uncle Bob.',
            ],
            [
                'isbn' => '9780201633610',
                'judul' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'penulis' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'penerbit' => 'Addison-Wesley Press',
                'tahun_terbit' => 1994,
                'kategori_id' => $kategoris['KAT-SAI']->id,
                'stok' => 18,
                'sinopsis' => 'Buku klasik tentang 23 pola desain perangkat lunak yang menjadi referensi wajib setiap developer.',
            ],
            [
                'isbn' => '9781491950357',
                'judul' => 'Python for Data Analysis',
                'penulis' => 'Wes McKinney',
                'penerbit' => "O'Reilly Media Press",
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoris['KAT-SAI']->id,
                'stok' => 22,
                'sinopsis' => 'Panduan komprehensif analisis data menggunakan Python, NumPy, pandas, dan Matplotlib.',
            ],
            [
                'isbn' => '9780262033848',
                'judul' => 'Introduction to Algorithms',
                'penulis' => 'Thomas H. Cormen',
                'penerbit' => 'MIT Press',
                'tahun_terbit' => 2009,
                'kategori_id' => $kategoris['KAT-SAI']->id,
                'stok' => 16,
                'sinopsis' => 'Buku teks algoritma paling komprehensif yang mencakup berbagai struktur data dan teknik pemrograman.',
            ],

            // Sejarah
            [
                'isbn' => '9780062316097',
                'judul' => 'Sapiens: A Brief History of Humankind',
                'penulis' => 'Yuval Noah Harari',
                'penerbit' => 'Harper Perennial Press',
                'tahun_terbit' => 2011,
                'kategori_id' => $kategoris['KAT-SEJ']->id,
                'stok' => 30,
                'sinopsis' => 'Narasi epik tentang perjalanan Homo sapiens dari Afrika hingga mendominasi bumi selama 70.000 tahun.',
            ],
            [
                'isbn' => '9780393357141',
                'judul' => 'Guns, Germs, and Steel',
                'penulis' => 'Jared Diamond',
                'penulis' => 'Jared Diamond',
                'penerbit' => 'W. W. Norton Press',
                'tahun_terbit' => 1997,
                'kategori_id' => $kategoris['KAT-SEJ']->id,
                'stok' => 11,
                'sinopsis' => 'Mengapa peradaban tertentu berkembang lebih pesat dari yang lain? Buku pemenang Pulitzer ini menjawabnya.',
            ],
            [
                'isbn' => '9780670020553',
                'judul' => 'Indonesia Etc.: Exploring the Improbable Nation',
                'penulis' => 'Elizabeth Pisani',
                'penerbit' => 'W. W. Norton Press',
                'tahun_terbit' => 2014,
                'kategori_id' => $kategoris['KAT-SEJ']->id,
                'stok' => 9,
                'sinopsis' => 'Perjalanan keliling Indonesia untuk memahami kompleksitas bangsa terbesar keempat di dunia.',
            ],

            // Sastra & Budaya
            [
                'isbn' => '9789792231946',
                'judul' => 'Laskar Pelangi',
                'penulis' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka Press',
                'tahun_terbit' => 2005,
                'kategori_id' => $kategoris['KAT-SAS']->id,
                'stok' => 35,
                'sinopsis' => 'Novel Indonesia paling terkenal tentang perjuangan sepuluh anak Belitung mendapatkan pendidikan layak.',
            ],
            [
                'isbn' => '9789799731821',
                'judul' => 'Bumi Manusia',
                'penulis' => 'Pramoedya Ananta Toer',
                'penerbit' => 'Hasta Mitra Press',
                'tahun_terbit' => 1980,
                'kategori_id' => $kategoris['KAT-SAS']->id,
                'stok' => 20,
                'sinopsis' => 'Karya masterpiece Pramoedya, mengisahkan perjuangan pemuda pribumi di masa kolonial Belanda.',
            ],

            // Agama & Spiritualitas
            [
                'isbn' => '9786020325651',
                'judul' => 'La Tahzan: Jangan Bersedih',
                'penulis' => 'Aidh al-Qarni',
                'penerbit' => 'Qisthi Press',
                'tahun_terbit' => 2004,
                'kategori_id' => $kategoris['KAT-AGM']->id,
                'stok' => 40,
                'sinopsis' => 'Buku motivasi islami yang mengajak pembaca untuk tidak berputus asa dan selalu bersyukur.',
            ],

            // Ekonomi & Bisnis
            [
                'isbn' => '9780743247351',
                'judul' => 'Good to Great: Why Some Companies Make the Leap and Others Don\'t',
                'penulis' => 'Jim Collins',
                'penerbit' => 'HarperBusiness Press',
                'tahun_terbit' => 2001,
                'kategori_id' => $kategoris['KAT-EKO']->id,
                'stok' => 17,
                'sinopsis' => 'Hasil riset lima tahun untuk mengidentifikasi faktor yang membuat perusahaan biasa menjadi luar biasa.',
            ],
            [
                'isbn' => '9780735224292',
                'judul' => 'Zero to One: Notes on Startups',
                'penulis' => 'Peter Thiel',
                'penerbit' => 'Crown Business Press',
                'tahun_terbit' => 2014,
                'kategori_id' => $kategoris['KAT-EKO']->id,
                'stok' => 23,
                'sinopsis' => 'Wawasan unik dari co-founder PayPal tentang cara membangun startup yang benar-benar inovatif.',
            ],
            [
                'isbn' => '9780743260244',
                'judul' => 'The Lean Startup',
                'penulis' => 'Eric Ries',
                'penerbit' => 'Crown Business Press',
                'tahun_terbit' => 2011,
                'kategori_id' => $kategoris['KAT-EKO']->id,
                'stok' => 19,
                'sinopsis' => 'Metodologi build-measure-learn untuk membangun bisnis baru secara efisien dengan meminimalkan pemborosan.',
            ],
            [
                'isbn' => '9780062301239',
                'judul' => 'Thinking, Fast and Slow',
                'penulis' => 'Daniel Kahneman',
                'penerbit' => 'Farrar, Straus and Giroux Press',
                'tahun_terbit' => 2011,
                'kategori_id' => $kategoris['KAT-EKO']->id,
                'stok' => 13,
                'sinopsis' => 'Nobel laureate membahas dua sistem berpikir manusia dan bagaimana keduanya memengaruhi keputusan ekonomi.',
            ],
            [
                'isbn' => '9780062457714',
                'judul' => 'The Power of Habit: Why We Do What We Do in Life and Business',
                'penulis' => 'Charles Duhigg',
                'penerbit' => 'Random House Press',
                'tahun_terbit' => 2012,
                'kategori_id' => $kategoris['KAT-EKO']->id,
                'stok' => 21,
                'sinopsis' => 'Ilmu di balik kebiasaan manusia dan cara mengubah kebiasaan buruk menjadi produktivitas bisnis.',
            ],
        ];

        foreach ($bukus as $buku) {
            Buku::firstOrCreate(
                ['isbn' => $buku['isbn']],
                $buku
            );
        }

        $this->command->info('✅ BukuSeeder: ' . count($bukus) . ' buku berhasil dibuat.');
    }
}
