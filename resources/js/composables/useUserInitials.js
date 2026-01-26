import { computed } from 'vue';

/**
 * Инициалы пользователя из имени (первые буквы слов, до 2 символов).
 * @param {import('vue').Ref<{ name?: string } | null>} userRef — ref/computed с объектом user
 * @returns {import('vue').ComputedRef<string>}
 */
export function useUserInitials(userRef) {
  return computed(() => {
    const name = userRef.value?.name;
    if (!name || typeof name !== 'string') return 'U';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return 'U';
    return parts
      .map((p) => p[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  });
}
