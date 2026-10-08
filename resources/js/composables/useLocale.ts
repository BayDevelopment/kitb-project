import { ref } from "vue";

export type LanguageCode = "id" | "en" | "zh";

export const currentLanguage = ref<LanguageCode>("id");

/**
 * Mengambil nilai berdasarkan bahasa aktif.
 *
 * Prioritas:
 * 1. Bahasa yang sedang dipilih.
 * 2. Bahasa Indonesia.
 * 3. Bahasa Inggris.
 * 4. Bahasa Mandarin.
 *
 * Mendukung field multilingual seperti:
 * judul_id, judul_en, judul_zh.
 *
 * Juga mendukung field dasar untuk kompatibilitas:
 * judul.
 */
export function localizedValue(
    object: Record<string, unknown> | null | undefined,
    field: string,
): string {
    if (!object) {
        return "";
    }

    const language = String(currentLanguage.value)
        .toLowerCase()
        .replace("-", "_");

    const suffix = language.startsWith("en")
        ? "_en"
        : language.startsWith("zh")
          ? "_zh"
          : "_id";

    const candidates = [
        `${field}${suffix}`,
        `${field}_id`,
        `${field}_en`,
        `${field}_zh`,
        field,
    ];

    for (const key of candidates) {
        const value = object[key];

        if (
            value !== null &&
            value !== undefined &&
            String(value).trim() !== ""
        ) {
            return String(value).trim();
        }
    }

    return "";
}
