<template>
    <TransitionRoot appear :show="modelValue" as="template">
      <Dialog as="div" @close="$emit('update:modelValue', false)" class="relative z-50">
        <TransitionChild
          enter="duration-200 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all">
                <div class="text-center">
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                    <Sparkle class="h-6 w-6 text-blue-600" />
                  </div>
                  <DialogTitle as="h3" class="mt-4 text-lg font-semibold leading-6 text-gray-900">
                    Upgrade to Plus
                  </DialogTitle>
                  <div class="mt-2">
                    <p class="text-sm  text-red-600">
                      For less than the cost of a pizza, get powerful tools to control your expenses and understand your spendings!
                    </p>
                  </div>
                </div>

                <div class="mt-6 space-y-4">
                  <div class="rounded-lg bg-blue-50 p-4">
                    <div class="flex items-center justify-between">
                      <div>
                        <h4 class="text-base font-semibold text-blue-900">Plus Plan</h4>
                        <p class="text-sm text-blue-700">$4.99/month</p>
                      </div>
                      <div class="text-right">
<!--                        <p class="text-xs text-blue-600">Have better control on your money</p>-->
<!--                        <p class="text-lg font-bold text-blue-900">$500/year</p>-->
                      </div>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Advanced expense analytics and insights (AI)</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">AI Access (Know yourself better)</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Priority support and updates</p>
                    </div>
                    <div class="flex items-start gap-3">
                      <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                      <p class="text-sm text-gray-600">Request any new feature</p>
                    </div>
                      <div class="flex items-start gap-3">
                          <CheckCircle class="h-5 w-5 text-green-500 flex-shrink-0 mt-0.5" />
                          <p class="text-sm text-gray-600">Free access to future features</p>
                      </div>
                  </div>

                  <div class="mt-6 flex flex-col gap-3">
                    <button
                      @click="handleUpgrade"
                      class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                    >
                      Upgrade Now
                    </button>
                    <button
                      @click="$emit('update:modelValue', false)"
                      class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                    >
                      Maybe Later
                    </button>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </template>

  <script>
  import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
  import { Sparkle, CheckCircle } from 'lucide-vue-next'

  export default {
    name: 'UpgradeModal',
    components: {
      Dialog,
      DialogPanel,
      DialogTitle,
      TransitionRoot,
      TransitionChild,
      Sparkle,
      CheckCircle
    },
    props: {
      modelValue: {
        type: Boolean,
        required: true
      }
    },
    emits: ['update:modelValue', 'upgrade'],
    methods: {
      handleUpgrade() {
        this.$emit('upgrade')
        this.$emit('update:modelValue', false)
      }
    }
  }
  </script>
