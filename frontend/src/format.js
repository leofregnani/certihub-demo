/** 'YYYY-MM-DD' -> localized date, without timezone shifts. */
export function fmtDate(value) {
  if (!value) return '—'
  const [y, m, d] = String(value).slice(0, 10).split('-').map(Number)
  return new Date(y, m - 1, d).toLocaleDateString()
}

export function fmtDateTime(value) {
  return value ? new Date(value).toLocaleString() : '—'
}

export const statusClass = {
  valid: 'bg-green-100 text-green-800',
  revoked: 'bg-red-100 text-red-800',
}
