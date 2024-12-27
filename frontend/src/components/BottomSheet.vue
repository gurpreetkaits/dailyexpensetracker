<!-- src/components/BottomSheet.vue -->
<template>
    <Teleport to="body">
      <transition name="fade">
        <div v-if="modelValue" class="fixed inset-0 bg-black bg-opacity-50 z-40" @click="$emit('update:modelValue', false)"></div>
      </transition>
      
      <transition name="slide">
        <div v-if="modelValue" class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-lg">
          <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto my-3"></div>
          <div class="px-4 pb-6">
            <slot></slot>
          </div>
        </div>
      </transition>
    </Teleport>
  </template>
  
  <script>
  export default {
    name: 'BottomSheet',
    props: {
      modelValue: {
        type: Boolean,
        required: true
      }
    },
    emits: ['update:modelValue']
  }
  </script>
  
  <style scoped>
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s ease;
  }
  
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  
  .slide-enter-active,
  .slide-leave-active {
    transition: transform 0.3s ease-in-out;
  }
  
  .slide-enter-from,
  .slide-leave-to {
    transform: translateY(100%);
  }
  
  .slide-enter-to,
  .slide-leave-from {
    transform: translateY(0);
  }
  </style>