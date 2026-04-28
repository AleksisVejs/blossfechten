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
