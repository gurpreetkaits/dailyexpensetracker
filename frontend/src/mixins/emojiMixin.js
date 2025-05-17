export const emojiMixin = {
  data() {
    return {
      emojis: {
        income: {
          salary: '💰',
          business: '💼',
          investment: '📈',
          extraincome: '💵',
          loan: '🏦',
          parentalleave: '👶',
          insurancepayout: '🏥',
          other: '📌'
        },
        expense: {
          foodanddrink: '🍽️',
          shopping: '🛍️',
          transport: '🚗',
          home: '🏠',
          billsandfees: '📝',
          entertainment: '🎬',
          car: '🚗',
          travel: '✈️',
          familyandpersonal: '👨‍👩‍👧‍👦',
          healthcare: '🏥',
          education: '📚',
          groceries: '🛒',
          gifts: '🎁',
          sportandhobbies: '⚽',
          beauty: '💄',
          work: '💼',
          food: '🍽️',
          drink: '🥤',
          restaurant: '🍽️',
          cafe: '☕',
          coffee: '☕',
          movie: '🎬',
          cinema: '🎬',
          games: '🎮',
          gaming: '🎮',
          music: '🎵',
          gym: '💪',
          fitness: '💪',
          sports: '⚽',
          medical: '🏥',
          health: '🏥',
          school: '📚',
          university: '📚',
          study: '📚',
          books: '📚',
          clothes: '👕',
          fashion: '👕',
          cosmetics: '💄',
          salon: '💇',
          haircut: '💇',
          taxi: '🚕',
          uber: '🚕',
          bus: '🚌',
          train: '🚂',
          metro: '🚇',
          subway: '🚇',
          plane: '✈️',
          flight: '✈️',
          vacation: '🏖️',
          holiday: '🏖️',
          hotel: '🏨',
          accommodation: '🏨',
          rent: '🏠',
          mortgage: '🏠',
          utilities: '💡',
          electricity: '⚡',
          water: '💧',
          gas: '🔥',
          internet: '🌐',
          wifi: '📶',
          phone: '📱',
          mobile: '📱',
          subscription: '📱',
          streaming: '🎬',
          netflix: '🎬',
          spotify: '🎵',
          amazon: '📦',
          online: '🛒',
          delivery: '🚚',
          takeout: '🍱',
          other: '📌'
        }
      }
    }
  },
  methods: {
    getCategoryEmoji(icon) {
      if (!icon) return '📌'
      // Check both income and expense categories
      return this.emojis.income[icon.toLowerCase()] || this.emojis.expense[icon.toLowerCase()] || '📌'
    },
    getTypeEmojis(type) {
      return this.emojis[type.toLowerCase()] || {}
    },
    getAllEmojis() {
      // Combine both income and expense emojis
      return {
        ...this.emojis.income,
        ...this.emojis.expense
      }
    }
  }
} 