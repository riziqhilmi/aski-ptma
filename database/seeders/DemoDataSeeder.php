<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Document;
use App\Models\Faq;
use App\Models\IntellectualProperty;
use App\Models\IssuedMembershipDocument;
use App\Models\MemberProfile;
use App\Models\Membership;
use App\Models\MembershipApplication;
use App\Models\MembershipApplicationHistory;
use App\Models\MembershipDocumentSetting;
use App\Models\MembershipRate;
use App\Models\OrganizationMember;
use App\Models\Payment;
use App\Models\SiteContent;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\MembershipDocumentService;
use Dompdf\Dompdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Password';

    public function run(): void
    {
        $admin = User::where('email', 'Admin@aski-ptma.test')->firstOrFail();
        $rates = $this->seedRates();
        $this->seedMembershipDocumentSettings();
        $members = $this->seedMembers();

        $memberships = [];
        foreach ($members as $index => $member) {
            $scenario = $this->scenarioFor($index);
            $application = $this->createApplication($member, $admin, $scenario, $index);
            $membership = null;

            if ($scenario === 'completed') {
                $membership = $this->createCompletedMembership($member, $application, $rates, $admin, $index);
                $memberships[$index] = $membership;
            } elseif ($scenario === 'waiting') {
                $this->createInvoice($member, $application, $rates, $index, false);
                if ($index % 3 !== 0) {
                    $this->createPayment($member, $application->invoice, $admin, $index, $index % 3 === 1 ? Payment::SUBMITTED : Payment::REJECTED);
                }
            }

            if ($index < 28) {
                $this->seedIntellectualProperties($member, $index);
            }
        }

        $this->seedRenewalApplications($members, $memberships, $rates, $admin);
        $this->seedTickets($members, $admin);
        $this->seedSiteContent($admin);
        $this->seedFaqs();
        $this->seedOrganizationExamples();
        $this->seedDocuments($admin);
        $this->seedArticles($admin);
    }

    private function seedRates(): array
    {
        return [
            'individual' => MembershipRate::create([
                'account_type' => 'individual',
                'name' => 'Tarif Demo Keanggotaan Individu Tahunan',
                'amount' => 750000,
                'duration_months' => 12,
                'active_from' => '2025-01-01',
                'active_until' => null,
            ]),
            'institution' => MembershipRate::create([
                'account_type' => 'institution',
                'name' => 'Tarif Demo Keanggotaan Institusi Tahunan',
                'amount' => 750000,
                'duration_months' => 12,
                'active_from' => '2025-01-01',
                'active_until' => null,
            ]),
        ];
    }

    private function seedMembers(): array
    {
        $institutions = [
            'Universitas Muhammadiyah Yogyakarta',
            'Universitas Muhammadiyah Surakarta',
            'Universitas Muhammadiyah Malang',
            'Universitas Ahmad Dahlan',
            'Universitas Muhammadiyah Jakarta',
            'Universitas Muhammadiyah Purwokerto',
            'Universitas Muhammadiyah Prof. Dr. HAMKA',
            'Universitas Muhammadiyah Sumatera Utara',
            'Universitas Muhammadiyah Palembang',
            'Universitas Muhammadiyah Makassar',
            'Universitas Muhammadiyah Jember',
            'Universitas Muhammadiyah Mataram',
            'Universitas Muhammadiyah Metro',
            'Universitas Muhammadiyah Riau',
            'Universitas Muhammadiyah Bengkulu',
            'Universitas Muhammadiyah Kendari',
            'Universitas Muhammadiyah Sidoarjo',
            'Universitas Muhammadiyah Semarang',
            'Universitas Muhammadiyah Gresik',
            'Universitas Muhammadiyah Klaten',
            'Universitas Muhammadiyah Aceh',
            'Universitas Muhammadiyah Pontianak',
            'Universitas Muhammadiyah Cirebon',
            'Universitas Muhammadiyah Tasikmalaya',
            'Universitas Muhammadiyah Palu',
            'Universitas Muhammadiyah Sorong',
            'Universitas Muhammadiyah Bone',
            'Universitas Muhammadiyah Enrekang',
            'Universitas Aisyiyah Yogyakarta',
            'Universitas Aisyiyah Surakarta',
            'Institut Teknologi dan Bisnis Ahmad Dahlan Jakarta',
            'Institut Teknologi dan Sains Muhammadiyah Kolaka',
            'Politeknik Muhammadiyah Yogyakarta',
            'Politeknik Muhammadiyah Makassar',
            'Sekolah Tinggi Ilmu Kesehatan Muhammadiyah Ciamis',
            'Sekolah Tinggi Ilmu Ekonomi Muhammadiyah Jakarta',
        ];
        $people = [
            'Aisyah Rahmawati', 'Fajar Hidayat', 'Nadia Putri Lestari', 'Rizky Maulana',
            'Dewi Anggraini', 'Ahmad Fauzan', 'Siti Nurhaliza', 'Bima Pratama',
            'Rina Kurniawati', 'Dimas Aditya', 'Fitri Handayani', 'Arif Setiawan',
        ];
        $people = array_slice($people, 0, 12);
        $cities = ['Yogyakarta', 'Surakarta', 'Malang', 'Jakarta', 'Purwokerto', 'Makassar', 'Jember', 'Semarang'];
        $members = [];

        foreach (array_merge($institutions, $people) as $index => $memberName) {
            $number = $index + 1;
            $type = $index < count($institutions) ? 'institution' : 'individual';
            $institution = $type === 'institution' ? $memberName : null;
            $name = $memberName;

            $member = User::create([
                'name' => $name,
                'email' => sprintf('member%02d@aski-ptma.test', $number),
                'password' => Hash::make(self::DEMO_PASSWORD),
                'role' => 'member',
                'email_verified_at' => now(),
                'notification_preferences' => [
                    'membership_updates' => true,
                    'invoice_updates' => true,
                    'payment_updates' => true,
                    'document_updates' => $number % 4 !== 0,
                    'news_updates' => $number % 3 === 0,
                ],
            ]);

            MemberProfile::create([
                'user_id' => $member->id,
                'account_type' => $type,
                'phone' => '+62 812-'.str_pad((string) (3000 + $number), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (1000 + $number), 4, '0', STR_PAD_LEFT),
                'address' => 'Jl. Pendidikan No. '.($number + 10).', '.($cities[$index % count($cities)]).', Indonesia',
                'institution_name' => $type === 'institution' ? $institution : null,
                'responsible_name' => $type === 'institution' ? $people[$index % count($people)] : null,
                'responsible_position' => $type === 'institution' ? 'Kepala Sentra Kekayaan Intelektual' : null,
            ]);

            $members[] = $member;
        }

        return $members;
    }

    private function scenarioFor(int $index): string
    {
        return match (true) {
            $index < 20 => 'completed',
            $index < 27 => 'waiting',
            $index < 32 => 'submitted',
            $index < 36 => 'revision',
            default => 'rejected',
        };
    }

    private function createApplication(User $member, User $admin, string $scenario, int $index): MembershipApplication
    {
        $submittedAt = $scenario === 'completed'
            ? ($index < 4 ? now()->subMonths(11) : ($index < 12 ? now()->subMonths(($index % 8) + 2) : now()->subMonths(16 + ($index % 8))))
            : now()->subDays(2 + ($index % 24));

        $application = MembershipApplication::create([
            'user_id' => $member->id,
            'application_type' => MembershipApplication::TYPE_NEW,
            'status' => match ($scenario) {
                'completed' => MembershipApplication::COMPLETED,
                'waiting' => MembershipApplication::APPROVED_WAITING_PAYMENT,
                'submitted' => MembershipApplication::SUBMITTED,
                'revision' => MembershipApplication::REVISION,
                default => MembershipApplication::REJECTED,
            },
            'admin_note' => match ($scenario) {
                'revision' => 'Mohon lengkapi dokumen pendukung dan perbarui informasi pemohon.',
                'rejected' => 'Permohonan simulasi ditolak karena data pendukung belum memenuhi persyaratan.',
                default => null,
            },
            'submitted_at' => $submittedAt,
        ]);
        $application->forceFill(['created_at' => $submittedAt, 'updated_at' => $submittedAt])->save();

        $this->addApplicationHistory($application, MembershipApplication::SUBMITTED, 'Permohonan dikirim oleh pemohon.', $member->id, $submittedAt);
        if (in_array($scenario, ['waiting', 'completed'], true)) {
            $approvedAt = $submittedAt->copy()->addDay();
            $this->addApplicationHistory($application, MembershipApplication::APPROVED_WAITING_PAYMENT, 'Permohonan disetujui dan menunggu pembayaran.', $admin->id, $approvedAt);
            if ($scenario === 'completed') {
                $this->addApplicationHistory($application, MembershipApplication::COMPLETED, 'Pembayaran telah diverifikasi.', $admin->id, $approvedAt->copy()->addDays(3));
            }
        } elseif ($scenario === 'revision') {
            $this->addApplicationHistory($application, MembershipApplication::REVISION, 'Pemohon diminta melengkapi informasi.', $admin->id, $submittedAt->copy()->addDay(), ['required_fields' => ['address', 'supporting_document']]);
        } elseif ($scenario === 'rejected') {
            $this->addApplicationHistory($application, MembershipApplication::REJECTED, 'Data pada permohonan simulasi belum dapat diverifikasi.', $admin->id, $submittedAt->copy()->addDay());
        }

        return $application;
    }

    private function addApplicationHistory(MembershipApplication $application, string $status, string $note, int $actorId, $at, ?array $revisionData = null): void
    {
        $history = MembershipApplicationHistory::create([
            'membership_application_id' => $application->id,
            'status' => $status,
            'note' => $note,
            'changed_by' => $actorId,
            'revision_data' => $revisionData,
        ]);
        $history->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
    }

    private function createInvoice(User $member, MembershipApplication $application, array $rates, int $index, bool $paid): \App\Models\Invoice
    {
        $issuedAt = $application->submitted_at->copy()->addDays(2);
        $rate = $rates[$member->memberProfile->account_type];
        $invoice = $application->invoice()->create([
            'user_id' => $member->id,
            'membership_rate_id' => $rate->id,
            'number' => sprintf('INV-%s-%06d', $issuedAt->format('Ym'), $application->id),
            'amount' => $rate->amount,
            'duration_months' => $rate->duration_months,
            'issued_at' => $issuedAt->toDateString(),
            'due_at' => $issuedAt->copy()->addDays(14)->toDateString(),
            'status' => $paid ? \App\Models\Invoice::PAID : \App\Models\Invoice::UNPAID,
        ]);
        $invoice->forceFill(['created_at' => $issuedAt, 'updated_at' => $issuedAt])->save();

        return $invoice;
    }

    private function createPayment(User $member, \App\Models\Invoice $invoice, User $admin, int $index, string $status): Payment
    {
        $submittedAt = $invoice->issued_at->copy()->addDays(2 + ($index % 5));
        $proofPath = 'payment-proofs/demo/'.sprintf('proof-%06d.pdf', $invoice->id);
        Storage::disk('local')->put($proofPath, $this->makePdf(
            'Bukti Pembayaran Simulasi',
            'Dokumen ini hanya untuk data demo ASKI-PTMA. Nomor tagihan: '.$invoice->number.'. Tidak merupakan bukti transfer atau transaksi perbankan yang sebenarnya.'
        ));

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $member->id,
            'proof_path' => $proofPath,
            'status' => $status,
            'admin_note' => $status === Payment::REJECTED ? 'Bukti simulasi perlu diperbaiki sebelum diverifikasi.' : null,
            'reviewed_by' => $status === Payment::SUBMITTED ? null : $admin->id,
            'reviewed_at' => $status === Payment::SUBMITTED ? null : $submittedAt->copy()->addDay(),
        ]);
        $payment->forceFill(['created_at' => $submittedAt, 'updated_at' => $submittedAt])->save();

        return $payment;
    }

    private function createCompletedMembership(User $member, MembershipApplication $application, array $rates, User $admin, int $index): Membership
    {
        $invoice = $this->createInvoice($member, $application, $rates, $index, true);
        $payment = $this->createPayment($member, $invoice, $admin, $index, Payment::VERIFIED);
        $payment->forceFill(['reviewed_at' => $application->submitted_at->copy()->addDays(5), 'updated_at' => $application->submitted_at->copy()->addDays(5)])->save();

        $startsAt = $application->submitted_at->copy()->addDays(5);
        $endsAt = $startsAt->copy()->addMonths(12)->subDay();
        $isExpired = $endsAt->lt(now()->startOfDay());
        $membership = Membership::create([
            'user_id' => $member->id,
            'membership_application_id' => $application->id,
            'invoice_id' => $invoice->id,
            'member_number' => sprintf('ASKI/%s/%04d', $startsAt->format('Y'), $index + 1),
            'starts_at' => $startsAt->toDateString(),
            'ends_at' => $endsAt->toDateString(),
            'status' => $isExpired ? 'expired' : 'active',
        ]);
        $membership->forceFill(['created_at' => $startsAt, 'updated_at' => $startsAt])->save();

        app(MembershipDocumentService::class)->issueAll($membership);

        return $membership;
    }

    private function seedRenewalApplications(array $members, array $memberships, array $rates, User $admin): void
    {
        foreach ([0, 1, 2, 3] as $index) {
            $member = $members[$index];
            $previousMembership = $memberships[$index];
            $status = $index < 2 ? MembershipApplication::SUBMITTED : MembershipApplication::APPROVED_WAITING_PAYMENT;
            $submittedAt = now()->subDays(5 + $index);
            $application = MembershipApplication::create([
                'user_id' => $member->id,
                'application_type' => MembershipApplication::TYPE_RENEWAL,
                'previous_membership_id' => $previousMembership->id,
                'status' => $status,
                'admin_note' => null,
                'submitted_at' => $submittedAt,
            ]);
            $application->forceFill(['created_at' => $submittedAt, 'updated_at' => $submittedAt])->save();
            $this->addApplicationHistory($application, MembershipApplication::SUBMITTED, 'Permohonan perpanjangan dikirim.', $member->id, $submittedAt);

            if ($status === MembershipApplication::APPROVED_WAITING_PAYMENT) {
                $this->addApplicationHistory($application, MembershipApplication::APPROVED_WAITING_PAYMENT, 'Perpanjangan disetujui dan menunggu pembayaran.', $admin->id, $submittedAt->copy()->addDay());
                $invoice = $this->createInvoice($member, $application, $rates, 40 + $index, false);
                if ($index === 2) {
                    $this->createPayment($member, $invoice, $admin, 40 + $index, Payment::SUBMITTED);
                }
            }
        }
    }

    private function seedIntellectualProperties(User $member, int $memberIndex): void
    {
        $types = ['Hak Cipta', 'Paten', 'Merek', 'Desain Industri', 'Rahasia Dagang'];
        $titles = [
            'Sistem Informasi Layanan Akademik Terintegrasi',
            'Perangkat Pemantau Kualitas Air Berbasis Sensor',
            'Platform Literasi dan Konsultasi Kekayaan Intelektual',
            'Desain Kemasan Produk Pangan Berbasis Bahan Lokal',
            'Metode Pengolahan Data Riset Perguruan Tinggi',
            'Aplikasi Pendampingan UMKM Halal dan Berkemajuan',
        ];
        $creators = ['Aisyah Rahmawati', 'Fajar Hidayat', 'Nadia Putri Lestari', 'Rizky Maulana', 'Dewi Anggraini', 'Ahmad Fauzan'];

        foreach (range(0, 1 + ($memberIndex % 2)) as $item) {
            $type = $types[($memberIndex + $item) % count($types)];
            $applicationDate = now()->subMonths(($memberIndex * 2 + $item) % 22)->subDays(($memberIndex + $item) % 24);
            $details = match ($type) {
                'Hak Cipta' => ['work_type' => 'Program Komputer'],
                'Paten' => ['patent_type' => 'Paten sederhana'],
                'Merek' => ['registration_number' => 'DEMO-MRK-'.sprintf('%04d', $memberIndex * 10 + $item + 1), 'goods_services_class' => (string) (9 + ($memberIndex % 30))],
                'Rahasia Dagang' => ['secrecy_status' => 'rahasia'],
                default => [],
            };

            IntellectualProperty::create([
                'user_id' => $member->id,
                'application_number' => 'DEMO-HKI-'.now()->format('Y').'-'.sprintf('%05d', $memberIndex * 3 + $item + 1),
                'application_date' => $applicationDate->toDateString(),
                'creator' => $type === 'Rahasia Dagang' ? null : $creators[($memberIndex + $item) % count($creators)],
                'rights_holder' => $member->memberProfile->institution_name ?: $member->name,
                'ip_type' => $type,
                'recordation_number' => $type === 'Hak Cipta' ? 'DEMO-CR-'.sprintf('%05d', $memberIndex * 3 + $item + 1) : null,
                'title' => $titles[($memberIndex + $item) % count($titles)],
                'first_published_at' => $type === 'Hak Cipta' ? $applicationDate->copy()->subMonths(1)->toDateString() : null,
                'protection_period' => 'Mengikuti dokumen pencatatan (data simulasi)',
                'type_details' => $details,
            ]);
        }
    }

    private function seedTickets(array $members, User $admin): void
    {
        $subjects = [
            'Pertanyaan mengenai status permohonan',
            'Bantuan pembaruan profil institusi',
            'Konfirmasi dokumen keanggotaan',
            'Kendala mengunggah bukti pembayaran',
            'Informasi tata cara perpanjangan',
            'Pertanyaan tentang pengisian data HKI',
        ];
        $responses = [
            'Terima kasih telah menghubungi kami. Silakan periksa kembali bagian status permohonan pada akun Anda.',
            'Data profil dapat diperbarui melalui menu Profil Member. Perubahan akan tersimpan setelah Anda menekan tombol Simpan.',
            'Dokumen keanggotaan tersedia pada menu Dokumen Keanggotaan setelah pembayaran diverifikasi.',
            'Pastikan file bukti berformat PDF, JPG, atau PNG dan dapat dibaca sebelum diunggah kembali.',
        ];

        foreach (range(0, 23) as $index) {
            $status = match ($index % 3) {
                0 => SupportTicket::STATUS_OPEN,
                1 => SupportTicket::STATUS_ANSWERED,
                default => SupportTicket::STATUS_CLOSED,
            };
            $createdAt = now()->subDays(1 + ($index * 3));
            $ticket = SupportTicket::create([
                'user_id' => $members[$index]->id,
                'subject' => $subjects[$index % count($subjects)],
                'message' => 'Halo Admin, saya ingin meminta bantuan terkait akun '.$members[$index]->email.'. Mohon arahan untuk langkah berikutnya. Pesan ini merupakan data simulasi.',
                'status' => $status,
                'admin_response' => $status === SupportTicket::STATUS_OPEN ? null : $responses[$index % count($responses)],
                'responded_by' => $status === SupportTicket::STATUS_OPEN ? null : $admin->id,
                'responded_at' => $status === SupportTicket::STATUS_OPEN ? null : $createdAt->copy()->addHours(4),
            ]);
            $ticket->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt->copy()->addHours($status === SupportTicket::STATUS_OPEN ? 0 : 4)])->save();
        }
    }

    private function seedSiteContent(User $admin): void
    {
        SiteContent::create([
            'key' => SiteContent::KEY_PROFILE,
            'title' => 'Profil ASKI-PTMA',
            'body' => 'Asosiasi Sentra Kekayaan Intelektual Perguruan Tinggi Muhammadiyah dan Aisyiyah (ASKI-PTMA) menjadi ruang kolaborasi untuk penguatan tata kelola, literasi, dan pengembangan kekayaan intelektual di lingkungan PTMA.',
            'updated_by' => $admin->id,
        ]);
        SiteContent::create([
            'key' => SiteContent::KEY_VISION_MISSION,
            'title' => 'Visi dan Misi',
            'body' => "Visi:\nMenjadi jejaring Sentra KI PTMA yang profesional, kolaboratif, dan berdampak.\n\nMisi:\n1. Mendorong tata kelola kekayaan intelektual yang berkelanjutan.\n2. Meningkatkan literasi dan pendampingan pencatatan KI.\n3. Memperluas kolaborasi riset dan hilirisasi inovasi.",
            'updated_by' => $admin->id,
        ]);
        SiteContent::create([
            'key' => SiteContent::KEY_CONTACT,
            'title' => 'Kontak ASKI-PTMA',
            'body' => 'Hubungi pengelola ASKI-PTMA untuk informasi keanggotaan dan layanan kekayaan intelektual. Kontak pada halaman ini merupakan data simulasi.',
            'metadata' => ['email' => 'info@aski-ptma.test', 'phone' => '', 'address' => '', 'instagram' => '', 'website' => ''],
            'updated_by' => $admin->id,
        ]);
    }

    private function seedFaqs(): void
    {
        $items = [
            ['Bagaimana cara mengajukan keanggotaan?', 'Lengkapi profil Member terlebih dahulu, kemudian buka menu Permohonan & Perpanjangan dan ikuti petunjuk pada halaman.'],
            ['Di mana saya dapat melihat status permohonan?', 'Status permohonan dan riwayat perubahannya dapat dilihat pada menu Permohonan & Perpanjangan.'],
            ['Bagaimana cara membayar tagihan?', 'Buka detail tagihan untuk melihat jumlah dan petunjuk pembayaran yang tersedia, lalu kirim bukti pembayaran melalui halaman tersebut.'],
            ['Berapa lama pemeriksaan bukti pembayaran?', 'Bukti pembayaran akan diperiksa oleh Admin. Status pemeriksaan dapat dipantau melalui detail tagihan.'],
            ['Di mana dokumen keanggotaan dapat diunduh?', 'Sertifikat dan kartu yang telah diterbitkan tersedia pada menu Dokumen Keanggotaan.'],
            ['Bagaimana cara mengajukan perpanjangan?', 'Menu perpanjangan akan mengikuti masa berlaku keanggotaan dan jadwal pengajuan yang tersedia pada akun Anda.'],
            ['Siapa yang dapat menginput data HKI?', 'Member dengan pembayaran keanggotaan yang telah diverifikasi dapat menginput dan mengelola data HKI.'],
            ['Bagaimana cara mengubah data profil?', 'Buka Pengaturan Akun, pilih Ubah Profil Member, lalu simpan perubahan setelah semua kolom wajib diisi.'],
            ['Apa yang harus dilakukan jika bukti pembayaran ditolak?', 'Periksa catatan Admin pada detail tagihan, lalu unggah bukti pembayaran yang telah diperbaiki.'],
            ['Bagaimana cara meminta bantuan Admin?', 'Gunakan formulir Hubungi Admin pada menu Bantuan & FAQ. Tiket dan jawabannya dapat dipantau pada riwayat tiket.'],
        ];

        foreach ($items as $index => [$question, $answer]) {
            Faq::create(['question' => $question, 'answer' => $answer, 'is_published' => true, 'sort_order' => $index + 1]);
        }
    }

    private function seedOrganizationExamples(): void
    {
        $roles = [
            ['Ketua Umum', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
            ['Wakil Ketua', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
            ['Sekretaris', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
            ['Bendahara', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
            ['Koordinator Bidang Kelembagaan', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
            ['Koordinator Bidang Layanan KI', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
            ['Koordinator Bidang Hilirisasi', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
            ['Koordinator Bidang Kemitraan', 'Contoh pengurus demo untuk pratinjau tampilan struktur organisasi.'],
        ];
        $names = ['Dr. Ahmad Fikri Ramadhan', 'Dr. Siti Nur Aisyah', 'M. Rizal Hidayat, S.H., M.H.', 'Dra. Rina Wulandari, M.Si.', 'Dr. Budi Santoso, M.Kom.', 'Ir. Nurul Huda, M.T.', 'Dr. Anisa Rahma, M.Pd.', 'Dr. Farhan Akbar, M.Sc.'];

        foreach ($roles as $index => [$position, $description]) {
            OrganizationMember::create([
                'name' => $names[$index].' (Demo)',
                'position' => $position,
                'description' => $description,
                'sort_order' => $index + 1,
                'is_active' => false,
            ]);
        }
    }

    private function seedDocuments(User $admin): void
    {
        $documents = [
            [Document::CATEGORY_GUIDE, 'Panduan Pengajuan Keanggotaan (Demo)', 'Ringkasan simulasi alur pengajuan keanggotaan ASKI-PTMA.'],
            [Document::CATEGORY_GUIDE, 'Panduan Pengisian Data HKI (Demo)', 'Petunjuk simulasi pengisian data kekayaan intelektual Member.'],
            [Document::CATEGORY_NOTIFICATION, 'Informasi Layanan Member (Demo)', 'Informasi simulasi mengenai fitur portal Member.'],
            [Document::CATEGORY_CERTIFICATE_TEMPLATE, 'Contoh Template Sertifikat (Demo)', 'Contoh dokumen simulasi, bukan sertifikat resmi.'],
            [Document::CATEGORY_CARD_TEMPLATE, 'Contoh Template Kartu Anggota (Demo)', 'Contoh dokumen simulasi, bukan kartu anggota resmi.'],
            [Document::CATEGORY_OTHER_TEMPLATE, 'Formulir Data Pendukung (Demo)', 'Formulir contoh untuk demonstrasi pengelolaan dokumen.'],
        ];

        foreach ($documents as $index => [$category, $title, $description]) {
            $path = 'documents/demo/'.Str::slug($title).'.pdf';
            Storage::disk('local')->put($path, $this->makePdf($title, $description.' Dokumen ini dibuat otomatis sebagai data demo dan tidak berlaku sebagai dokumen resmi.'));
            Document::create([
                'uploaded_by' => $admin->id,
                'title' => $title,
                'category' => $category,
                'status' => Document::STATUS_PUBLISHED,
                'description' => $description.' Berkas simulasi untuk demonstrasi aplikasi.',
                'file_path' => $path,
                'original_filename' => Str::slug($title).'.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => Storage::disk('local')->size($path),
                'published_at' => now()->subDays($index * 4),
            ]);
        }
    }

    private function seedArticles(User $admin): void
    {
        $articles = [
            ['Praktik Baik Pemetaan Potensi Kekayaan Intelektual Kampus', 'Contoh artikel simulasi mengenai pemetaan hasil riset dan karya sivitas akademika.'],
            ['Membangun Budaya Inovasi di Lingkungan Perguruan Tinggi', 'Contoh artikel simulasi tentang kolaborasi dosen, mahasiswa, dan pengelola inovasi.'],
            ['Mengenal Ragam Kekayaan Intelektual untuk Hasil Riset', 'Materi pengantar simulasi mengenai beberapa jenis kekayaan intelektual.'],
            ['Kolaborasi Sentra KI dalam Mendukung Hilirisasi Riset', 'Contoh artikel simulasi tentang peran jejaring Sentra KI dalam kolaborasi riset.'],
            ['Penguatan Dokumentasi Karya dan Inovasi Akademik', 'Contoh artikel simulasi mengenai pencatatan dan dokumentasi karya akademik.'],
            ['Pendampingan Kekayaan Intelektual untuk UMKM Binaan Kampus', 'Contoh artikel simulasi mengenai pendampingan inovasi produk UMKM.'],
            ['Literasi Kekayaan Intelektual bagi Sivitas Akademika', 'Contoh artikel simulasi untuk edukasi awal mengenai pengelolaan kekayaan intelektual.'],
            ['Jejaring PTMA dan Pengembangan Inovasi Berkelanjutan', 'Contoh artikel simulasi mengenai kerja sama dan pertukaran praktik baik di PTMA.'],
        ];

        foreach ($articles as $index => [$title, $excerpt]) {
            $publishedAt = now()->subDays(5 + ($index * 9));
            Article::create([
                'author_id' => $admin->id,
                'title' => '[DEMO] '.$title,
                'slug' => 'demo-'.Str::slug($title),
                'excerpt' => $excerpt,
                'body' => "<p><strong>Konten simulasi.</strong> Tulisan ini dibuat untuk mengisi data demo portal ASKI-PTMA dan bukan publikasi resmi.</p><p>{$excerpt}</p><p>Dalam penerapan nyata, setiap proses pencatatan dan pengelolaan kekayaan intelektual perlu mengikuti dokumen, kebijakan, serta ketentuan yang berlaku.</p>",
                'status' => $index < 5 ? Article::STATUS_PUBLISHED : Article::STATUS_DRAFT,
                'published_at' => $index < 5 ? $publishedAt : null,
            ]);
        }
    }

    private function seedMembershipDocumentSettings(): void
    {
        MembershipDocumentSetting::create([
            'type' => IssuedMembershipDocument::CERTIFICATE,
            'title' => 'SERTIFIKAT KEANGGOTAAN (DEMO)',
            'number_prefix' => 'DEMO-CERT/ASKI',
            'background_color' => '#0b6254',
            'accent_color' => '#d4a72c',
        ]);
        MembershipDocumentSetting::create([
            'type' => IssuedMembershipDocument::CARD,
            'title' => 'KARTU ANGGOTA (DEMO)',
            'number_prefix' => 'DEMO-ASKI',
            'background_color' => '#0b6254',
            'accent_color' => '#d4a72c',
        ]);
    }

    private function makePdf(string $title, string $body): string
    {
        $safeTitle = e($title);
        $safeBody = e($body);
        $html = '<html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#243b36;padding:36px}h1{color:#0b6254;font-size:22px}.mark{margin-top:28px;padding:14px;border:1px solid #d4a72c;background:#fffbea;color:#665314;font-size:12px}</style></head><body><h1>'.$safeTitle.'</h1><p>'.$safeBody.'</p><div class="mark">DATA SIMULASI — TIDAK BERLAKU SEBAGAI DOKUMEN ATAU BUKTI TRANSAKSI RESMI</div></body></html>';
        $pdf = new Dompdf();
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();

        return $pdf->output();
    }
}
