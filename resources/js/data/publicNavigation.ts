export interface PublicNavigationItem {
    label: string;
    href: string;
    badge?: string;
}

export interface PublicNavigationGroup {
    label: string;
    items: PublicNavigationItem[];
}

export interface PublicSocialLink {
    label: string;
    type: "instagram" | "tiktok" | "youtube";
    href: string;
}

/**
 * CTA utama website publik.
 */
export const visitUrl = "/ajukan-kunjungan";

/**
 * Navigasi utama website publik KITB.
 *
 * Data ini digunakan bersama oleh:
 * - PublicNavbar.vue
 * - PublicFooter.vue
 */
export const navGroups: PublicNavigationGroup[] = [
    {
        label: "Profil Perusahaan",
        items: [
            {
                label: "Tentang Kami",
                href: "/profil-perusahaan/tentang-kami",
            },
            {
                label: "Visi, Misi & Nilai",
                href: "/profil-perusahaan/visi-misi",
            },
            {
                label: "Struktur Perusahaan",
                href: "/profil-perusahaan/struktur-perusahaan",
            },
            {
                label: "Anak Usaha",
                href: "/profil-perusahaan/anak-usaha",
            },
        ],
    },

    {
        label: "Kawasan Industri",
        items: [
            {
                label: "Profil Kawasan",
                href: "/kawasan/profil-kawasan",
            },
            {
                label: "Infrastruktur",
                href: "/kawasan/infrastruktur",
            },
            {
                label: "Fasilitas",
                href: "/kawasan/fasilitas",
            },
            {
                label: "Peta Kawasan",
                href: "/kawasan/peta-kawasan",
            },
        ],
    },

    {
        label: "Hubungan Investor",
        items: [
            {
                label: "Ajukan Kunjungan Lahan",
                href: visitUrl,
            },
            {
                label: "Peluang Investasi",
                href: "/hubungan-investor/peluang-investasi",
            },
            {
                label: "Ease of Doing Business",
                href: "/hubungan-investor/ease-of-doing-business",
            },
            {
                label: "Rute Pelayaran & Lokasi",
                href: "/hubungan-investor/rute-pelayaran-lokasi",
            },
        ],
    },

    {
        label: "Pusat Informasi",
        items: [
            {
                label: "Berita",
                href: "/berita",
            },
            {
                label: "Galeri",
                href: "/galeri",
            },
            {
                label: "Publikasi",
                href: "/publikasi",
            },
            {
                label: "Karier",
                href: "/karier",
                badge: "Join Us",
            },
        ],
    },
];

/**
 * Link kontak.
 *
 * Menggunakan /#kontak agar tetap dapat menuju section
 * kontak ketika user sedang berada di halaman selain homepage.
 */
export const kontakLink = {
    href: "/#kontak",
    label: "Kontak",
};

/**
 * Social media resmi KITB.
 *
 * Ganti href setelah URL akun resmi sudah tersedia.
 */
export const socials: PublicSocialLink[] = [
    {
        label: "Instagram",
        type: "instagram",
        href: "#",
    },
    {
        label: "TikTok",
        type: "tiktok",
        href: "#",
    },
    {
        label: "YouTube",
        type: "youtube",
        href: "#",
    },
];
