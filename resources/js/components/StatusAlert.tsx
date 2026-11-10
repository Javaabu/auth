import { Alert } from "@javaabu/forms";

export function StatusAlert({ status }: { status?: string | null }) {
  if (!status) {
    return null;
  }

  return (
    <Alert type="success" data-auth-status>
      {status}
    </Alert>
  );
}
