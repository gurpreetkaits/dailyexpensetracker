import dayjs from "dayjs";

export const numberMixin = {
  methods: {
    formatCurrency(amount, currencyCode) {
      try {
        return new Intl.NumberFormat("en-US", {
          style: "currency",
          currency: currencyCode || "USD",
        }).format(amount);
      } catch (error) {
        return amount.toFixed(2);
      }
    },
    capitalizeFirstLetter(str) {
      if (!str) return "";
      return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    },
    formatDate(date, format = "YYYY-MM-DD") {
      return dayjs(date).format(format);
    },
    getDate(format = "YYYY-MM-DD") {
      return dayjs().format(format);
    },
  },
};
