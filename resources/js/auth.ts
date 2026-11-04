/**
 * For the Blade pages: forms marked data-confirm ask before they submit.
 */
function onSubmit(event: Event): void {
  const form = event.target as HTMLFormElement | null;
  const message = form?.dataset?.confirm;

  if (message && !window.confirm(message)) {
    event.preventDefault();
    event.stopImmediatePropagation();
  }
}

export function init(root: Document = document): void {
  root.addEventListener("submit", onSubmit, true);
}

if (typeof document !== "undefined" && !(window as { __javaabuAuth?: boolean }).__javaabuAuth) {
  (window as { __javaabuAuth?: boolean }).__javaabuAuth = true;
  init();
}
