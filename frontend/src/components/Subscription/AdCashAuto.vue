<template>
    <div ref="adContainer" class="adcash-slot"></div>
  </template>
  
  <script setup>
  import { onMounted, ref } from 'vue'
  
  const adContainer = ref(null)
  
  onMounted(() => {
    if (!document.getElementById('aclib')) {
      const script = document.createElement('script')
      script.id = 'aclib'
      script.type = 'text/javascript'
      script.src = '//acscdn.com/script/aclib.js'
      script.onload = () => {
        if (window.aclib) {
          window.aclib.runAutoTag({ zoneId: 'ndqhddbyxj' })
        }
      }
      document.body.appendChild(script)
    } else if (window.aclib) {
      window.aclib.runAutoTag({ zoneId: 'ndqhddbyxj' })
    }
  })
  </script>