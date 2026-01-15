import { ref, computed } from 'vue'

const pricingMap = {
    'IN': {
        currency: 'INR',
        monthly: 249,
        yearly: 2499,
        locale: 'en-IN'
    },
    'US': {
        currency: 'USD',
        monthly: 2.99,
        yearly: 29.99,
        locale: 'en-US'
    }
}

const defaultPricing = {
    currency: 'USD',
    monthly: 2.99,
    yearly: 29.99,
    locale: 'en-US'
}

const LOCATION_CACHE_KEY = 'user_country_cache'
const CACHE_TTL_MS = 24 * 60 * 60 * 1000 // 24 hours

function getCachedLocation() {
    try {
        const cached = localStorage.getItem(LOCATION_CACHE_KEY)
        if (cached) {
            const { country, timestamp } = JSON.parse(cached)
            if (Date.now() - timestamp < CACHE_TTL_MS) {
                return country
            }
        }
    } catch (e) {
        // Ignore localStorage errors
    }
    return null
}

function setCachedLocation(country) {
    try {
        localStorage.setItem(LOCATION_CACHE_KEY, JSON.stringify({
            country,
            timestamp: Date.now()
        }))
    } catch (e) {
        // Ignore localStorage errors
    }
}

export function useCurrency() {
    const cachedCountry = getCachedLocation()
    const userCountry = ref(cachedCountry || 'US')
    const isLoading = ref(!cachedCountry)

    const fetchUserLocation = async () => {
        // Skip API call if we have a valid cached value
        if (getCachedLocation()) {
            isLoading.value = false
            return
        }

        try {
            const response = await fetch('https://ipapi.co/json/')
            const data = await response.json()
            userCountry.value = data.country_code
            setCachedLocation(data.country_code)
        } catch (error) {
            console.error('Error fetching location:', error)
            userCountry.value = 'US'
        } finally {
            isLoading.value = false
        }
    }

    const pricing = computed(() => {
        return pricingMap[userCountry.value] || defaultPricing
    })

    const formatPrice = (amount) => {
        const formatter = new Intl.NumberFormat(pricing.value.locale, {
            style: 'currency',
            currency: pricing.value.currency,
            minimumFractionDigits: pricing.value.currency === 'INR' ? 0 : 2
        })
        return formatter.format(amount)
    }

    const monthlyPrice = computed(() => formatPrice(pricing.value.monthly))
    const yearlyPrice = computed(() => formatPrice(pricing.value.yearly))
    const monthlyEquivalent = computed(() => {
        const amount = pricing.value.yearly / 12
        return formatPrice(amount)
    })

    return {
        isLoading,
        userCountry,
        monthlyPrice,
        yearlyPrice,
        monthlyEquivalent,
        fetchUserLocation,
        currency: computed(() => pricing.value.currency)
    }
}