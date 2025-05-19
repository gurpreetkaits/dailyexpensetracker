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

export function useCurrency() {
    const userCountry = ref('US')
    const isLoading = ref(true)

    const fetchUserLocation = async () => {
        try {
            const response = await fetch('https://ipapi.co/json/')
            const data = await response.json()
            userCountry.value = data.country_code
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