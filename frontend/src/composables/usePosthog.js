import posthog from 'posthog-js'

export function usePostHog() {
  posthog.init('phc_aHfozAqV6TlGwu7ysNNiNGQ45Cgi7YLETXDuUwBVsAX', {
    api_host: 'https://us.i.posthog.com',
    person_profiles: 'identified_only'
  })

  return {
    posthog
  }
}