// The canonical public origin, in one place. Everything that has to name the
// site absolutely — canonical links, JSON-LD, sitemap-facing URLs — reads it
// from here. It used to be hardcoded per file, and had drifted to a domain the
// club does not own.
export const SITE_URL = 'https://blossfechtenriga.com'

// The hall the club actually trains in. The contact page shows it and the
// schedule's JSON-LD claims it as the Event location, so it lives here rather
// than in two places that can drift apart.
export const CLUB_VENUE = {
  streetAddress: 'Ādmiņu iela 4',
  addressLocality: 'Rīga',
  postalCode: 'LV-1009',
  addressCountry: 'LV',
}

export const CLUB_ADDRESS_LINE =
  `${CLUB_VENUE.streetAddress}, ${CLUB_VENUE.addressLocality}, ${CLUB_VENUE.postalCode}`

// Google wants an image on every Event, at least 696px wide. These two are the
// only assets that clear that bar; the logo is 224px square and does not.
export const EVENT_IMAGES = [
  `${SITE_URL}/img/meyer/four-hews.jpg`,
  `${SITE_URL}/img/meyer/cover.jpg`,
]

export const ORGANIZATION_SCHEMA = {
  '@type': 'Organization',
  name: 'Blossfechten Riga',
  url: SITE_URL,
  logo: `${SITE_URL}/img/logo.png`,
}
