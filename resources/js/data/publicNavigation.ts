export interface PublicNavigationItem {
    label: string;
    translationKey?: string;
    href: string;
    badge?: string;
    badgeTranslationKey?: string;
}

export interface PublicNavigationGroup {
    label: string;
    translationKey?: string;
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
        translationKey: "navigation.company_profile",
        items: [
            {
                label: "Tentang Kami",
                translationKey: "navigation.about",
                href: "/profil-perusahaan/tentang-kami",
            },
            {
                label: "Visi, Misi & Nilai",
                translationKey: "navigation.vision_mission_values",
                href: "/profil-perusahaan/visi-misi",
            },
            {
                label: "Struktur Perusahaan",
                translationKey: "navigation.company_structure",
                href: "/profil-perusahaan/struktur-perusahaan",
            },
            {
                label: "Anak Usaha",
                translationKey: "navigation.subsidiaries",
                href: "/profil-perusahaan/anak-usaha",
            },
        ],
    },

    {
        label: "Kawasan Industri",
        translationKey: "navigation.industrial_area",
        items: [
            {
                label: "Profil Kawasan",
                translationKey: "navigation.area_profile",
                href: "/kawasan/profil-kawasan",
            },
            {
                label: "Infrastruktur",
                translationKey: "navigation.infrastructure",
                href: "/kawasan/infrastruktur",
            },
            {
                label: "Fasilitas",
                translationKey: "navigation.facilities",
                href: "/kawasan/fasilitas",
            },
            {
                label: "Peta Kawasan",
                translationKey: "navigation.area_map",
                href: "/kawasan/peta-kawasan",
            },
        ],
    },

    {
        label: "Hubungan Investor",
        translationKey: "navigation.investor_relations",
        items: [
            {
                label: "Peluang Investasi",
                translationKey: "navigation.investment_opportunities",
                href: "/hubungan-investor/peluang-investasi",
            },
            {
                label: "Ease of Doing Business",
                translationKey: "navigation.ease_of_doing_business",
                href: "/hubungan-investor/ease-of-doing-business",
            },
            {
                label: "Kunjungan Lahan",
                translationKey: "navigation.land_visit",
                href: visitUrl,
            },
            {
                label: "Rute Pelayaran & Lokasi",
                translationKey: "navigation.shipping_routes_location",
                href: "/hubungan-investor/rute-pelayaran-lokasi",
            },
        ],
    },

    {
        label: "Pusat Informasi",
        translationKey: "navigation.information_center",
        items: [
            {
                label: "Berita",
                translationKey: "navigation.news",
                href: "/berita",
            },
            {
                label: "Galeri",
                translationKey: "navigation.gallery",
                href: "/galeri",
            },
            {
                label: "Lowongan",
                translationKey: "navigation.jobs",
                href: "/karier",
                badge: "Join Us",
                badgeTranslationKey: "navigation.join_us",
            },
        ],
    },
];

/**
 * Link kontak publik.
 *
 * Mengarah langsung ke halaman Kontak agar dapat
 * diakses dari halaman mana pun di website.
 */
export const kontakLink = {
    href: "/kontak",
    label: "Kontak",
    translationKey: "navigation.contact",
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
