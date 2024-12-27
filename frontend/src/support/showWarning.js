export default function (warnings) {
    const warningKeys = Object.keys(warnings)
    if (warningKeys.length === 0) return
    warningKeys.forEach(key => {
      if (typeof warnings[key] === 'string') this.$notification.$emit('success', { html: warnings[key] })
      else if (Array.isArray(warnings[key])) {
        for (const keyElement of warnings[key]) {
          if (keyElement) {
            for (const messages in keyElement) {
              let html = ''
              keyElement[messages].forEach(({message}) => {
                if (message) html += `<span>${message}</span><br>`
              })
              if (html.length) this.$notification.$emit('warning', { html })
            }
          }
        }
      }
    })
  }
  