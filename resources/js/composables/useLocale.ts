import { ref } from "vue";

export type LanguageCode = "id" | "en" | "zh";

export const currentLanguage = ref<LanguageCode>("id");

export function localizedValue(
    object: Record<string, unknown> | null | undefined,
    field: string,
): string {
    if (!object) {
        return "";
    }

    const language = currentLanguage.value;

    const key =
        language === "en"
            ? `${field}_en`
            : language === "zh"
              ? `${field}_zh`
              : field;

    const value = object[key];

    // Gunakan translation jika tersedia
    if (value !== null && value !== undefined && String(value).trim() !== "") {
        return String(value);
    }

    // Fallback ke Bahasa Indonesia
    const fallback = object[field];

    if (
        fallback !== null &&
        fallback !== undefined &&
        String(fallback).trim() !== ""
    ) {
        return String(fallback);
    }

    return "";
}
