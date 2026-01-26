import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

const STORAGE_KEY = 'theme';
const LIGHT = 'light';
const DARK = 'dark';

/**
 * Store темы (светлая / тёмная).
 * Синхронизирует data-theme и class "dark" на <html> с localStorage.
 * Tailwind dark: срабатывает по классу .dark (darkMode: 'class').
 * @see https://pinia.vuejs.org/
 * @see https://tailwindcss.com/docs/dark-mode
 */
export const useThemeStore = defineStore('theme', () => {
  const theme = ref(LIGHT);

  const isDark = computed(() => theme.value === DARK);

  function applyToDocument() {
    const html = document.documentElement;
    if (theme.value === DARK) {
      html.setAttribute('data-theme', DARK);
      html.classList.add('dark');
      html.style.colorScheme = 'dark';
    } else {
      html.setAttribute('data-theme', LIGHT);
      html.classList.remove('dark');
      html.style.colorScheme = 'light';
    }
  }

  function setTheme(value) {
    if (value !== LIGHT && value !== DARK) return;
    theme.value = value;
    try {
      localStorage.setItem(STORAGE_KEY, value);
    } catch (e) {
      /* ignore */
    }
    applyToDocument();
  }

  function toggleTheme() {
    setTheme(theme.value === DARK ? LIGHT : DARK);
  }

  /**
   * Инициализация: читает localStorage и применяет тему к document.
   * Вызывать при старте приложения (app.js). Ранний скрипт в blade
   * уже выставляет тему до загрузки Vue, чтобы избежать мигания.
   */
  function init() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved === DARK || saved === LIGHT) {
        theme.value = saved;
      } else {
        theme.value = document.documentElement.getAttribute('data-theme') === DARK ? DARK : LIGHT;
      }
    } catch {
      theme.value = LIGHT;
    }
    applyToDocument();
  }

  return {
    theme,
    isDark,
    setTheme,
    toggleTheme,
    init,
  };
});
