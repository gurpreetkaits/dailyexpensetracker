<template>
  <div ref="adContainer" class="adcash-slot"></div>
</template>

<script setup>
import { onMounted, ref } from 'vue'

const adContainer = ref(null)

onMounted(() => {
  // Check if the script already exists
  if (!document.getElementById('aclib')) {
    const script = document.createElement('script')
    script.id = 'aclib'
    script.type = 'text/javascript'
    script.src = 'https://acscdn.com/script/aclib.js' // ✅ Use HTTPS for security

    script.onload = () => {
      if (window.aclib) {
        window.aclib.runInPagePush({
          zoneId: '9968262',
          maxAds: 2 // ✅ match your original config if needed
        })
      }
    }

    document.body.appendChild(script)
  } else if (window.aclib) {
    window.aclib.runInPagePush({
      zoneId: '9968262',
      maxAds: 2
    })
  }
})
</script>