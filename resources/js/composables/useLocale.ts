import { ref } from "vue";

export type LanguageCode = "id" | "en" | "zh";

export const currentLanguage = ref<LanguageCode>("id");
