// have to use function rather than arrow function, because we use it with .call(this, ...), and arrow functions
// doesn't support that
const LOCKED_RESOURCE_STATUS = 423;

export default function (e, messageHandler = null) {
  if (e && e.response && e.response.data && e.response.data.errors) {
    let errors = e.response.data.errors;

    /*
    errors structure is like this:
    errors = {
      lengthError: [{
        code: 'maxLengthRequired',
        message: 'Wrong length'
      }],
      emailError: [{
        code: 'correctEmailRequired',
        message: 'Wrong email format.'
      }]
    }

    or like this
    errors = {
      12412424: {
        item.1.errors: [{
          code: 'correctEmailRequired',
          message: 'Wrong email format.'
        }],
        item.2.errors: [{
          code: 'correctEmailRequired',
          message: 'Wrong email format.'
        }]
      }
    }
    */
    if (e.response && e.response.status === LOCKED_RESOURCE_STATUS) {
      return handleLockedResourceError(this, errors);
    }

    for (let key in errors) {
      if (Array.isArray(errors[key])) {
        errors[key].forEach((error) => {
          if (error.hasOwnProperty("message")) {
            let message = error.message;
            if (messageHandler) message = messageHandler(message);
            this.$notification.$emit("danger", { html: message });
          } else if (typeof error === "string" && Boolean(error))
            this.$notification.$emit("danger", { html: error });
          else if (Object.keys(error).length) {
            for (let keyName in error) {
              error[keyName].forEach((err) => {
                this.$notification.$emit("danger", { html: err.message });
              });
            }
          }
        });
      } else {
        // else it is expected to be an object
        if (e.response.data.message) {
          this.$notification.$emit("danger", { html: e.response.data.message });
        }
      }
    }
  } else if (e && e.response && e.response.data && e.response.data.message) {
    this.$notification.$emit("danger", { html: e.response.data.message });
  } else if (e && e.response && e.response.message) {
    this.$notification.$emit("danger", { html: e.response.message });
  } else if (e.message) {
    // handling for sales order import and potentially other imports
    this.$notification.$emit("danger", { html: e.message });
    if (e.data && Object.values(e.data).length) {
      Object.entries(e.data).forEach((entry) => {
        let [itemId, errorsForItem] = entry;
        let message = `<span>${itemId}: `;
        Object.values(errorsForItem).forEach((errors, itemIndex) => {
          errors.forEach((e, index) => {
            message +=
              (Object.values(errorsForItem).length > 1
                ? `${itemIndex + 1})`
                : "") + `${e.message}<br>`;
          });
        });
        message += `</span>`;
        this.$notification.$emit("danger", { html: message });
      });
    }
  } else if (e) {
    this.$notification.$emit("danger");
  }
}

const handleLockedResourceError = (context, errors) => {
  if (!errors.resource) return;
  switch (errors.resource) {
    case "stock take":
      return handleStockTakeLockError(context, errors.id);
  }
};

// Stock Take Lock error handler
const handleStockTakeLockError = (context, stockTakeId) => {
  // Show the stock take modal
  context.$notification.$emit("stock-take-lock", {
    stock_take_id: stockTakeId,
  });
};
