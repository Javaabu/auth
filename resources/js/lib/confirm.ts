import type { FormEvent } from "react";

/** A capture handler that stops a form submitting unless the user confirms */
export function confirmSubmit(message: string) {
  return (event: FormEvent) => {
    if (!window.confirm(message)) {
      event.preventDefault();
      event.stopPropagation();
    }
  };
}
