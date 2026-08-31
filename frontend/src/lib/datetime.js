export function parseLocalDateTime(value) {
  if (value instanceof Date) return value
  if (typeof value !== 'string') return new Date(value)

  const match = value.match(
    /^(\d{4})-(\d{2})-(\d{2})(?:[T\s](\d{2}):(\d{2})(?::(\d{2}))?)?$/
  )

  if (!match) return new Date(value)

  const [, year, month, day, hours = '00', minutes = '00', seconds = '00'] = match

  return new Date(
    Number(year),
    Number(month) - 1,
    Number(day),
    Number(hours),
    Number(minutes),
    Number(seconds),
  )
}

export function formatDateTimeLocal(value) {
  const date = parseLocalDateTime(value)
  if (Number.isNaN(date.getTime())) return ''

  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')

  return `${year}-${month}-${day}T${hours}:${minutes}`
}

// The API serialises session times as bare wall-clock strings with no offset —
// "2026-09-02T18:00:00" — which is fine for the page, because the reader is in
// Riga. JSON-LD is read by a crawler that is not, and a startDate without an
// offset lets Google pick its own timezone and advertise the session hours off.
// The club trains in Riga, so stamp Europe/Riga's offset for that date.
const RIGA_OFFSET_FMT = new Intl.DateTimeFormat('en-US', {
  timeZone: 'Europe/Riga',
  timeZoneName: 'longOffset',
})

export function toRigaIsoString(value) {
  if (typeof value !== 'string' || !value) return value

  // Already carries an offset or a Z — leave it alone.
  if (/([+-]\d{2}:?\d{2}|Z)$/.test(value)) return value

  const asUtc = new Date(`${value.replace(' ', 'T')}Z`)
  if (Number.isNaN(asUtc.getTime())) return value

  const name = RIGA_OFFSET_FMT.formatToParts(asUtc)
    .find(part => part.type === 'timeZoneName')?.value || ''
  // "GMT+03:00" → "+03:00"; plain "GMT" means UTC.
  const offset = name.replace('GMT', '') || '+00:00'

  return `${value.replace(' ', 'T')}${offset}`
}
