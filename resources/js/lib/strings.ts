import type { AuthStrings } from "../types";

/** A translated string, with :placeholders replaced */
export function t(strings: AuthStrings, key: string, replace: Record<string, string> = {}): string {
  let text = strings[key] ?? key;

  for (const [name, value] of Object.entries(replace)) {
    text = text.replaceAll(`:${name}`, value);
  }

  return text;
}
