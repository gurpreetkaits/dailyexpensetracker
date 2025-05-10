<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-lg font-medium leading-6 text-gray-900 flex justify-between items-center"
                            >
                                <span>{{ title }}</span>
                                <button
                                    @click="closeModal"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
                                    aria-label="Close modal"
                                >
                                    <XIcon class="h-6 w-6" />
                                </button>
                            </DialogTitle>

                            <div class="mt-4">
                                <slot></slot>
                            </div>

                            <div class="mt-6">
                                <slot name="footer"></slot>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script>
import {
    TransitionRoot,
    TransitionChild,
    Dialog,
    DialogPanel,
    DialogTitle,
} from '@headlessui/vue';
import { XIcon } from 'lucide-vue-next'; // Using XIcon for the close button

export default {
    name: 'GlobalModal',
    components: {
        TransitionRoot,
        TransitionChild,
        Dialog,
        DialogPanel,
        DialogTitle,
        XIcon, // Icon for the close button
    },
    props: {
        // Controls the visibility of the modal
        show: {
            type: Boolean,
            required: true,
        },
        // Title displayed in the modal header
        title: {
            type: String,
            default: 'Modal Title', // Default title if none is provided
        },
    },
    emits: ['close'], // Declares the 'close' event that this component can emit
    setup(props, { emit }) {
        // Function to emit the 'close' event
        // This will typically be used to set the 'show' prop to false in the parent component
        const closeModal = () => {
            emit('close');
        };

        return {
            closeModal,
        };
    },
};
</script>

<style scoped>
/* Add any specific styles for the BaseModal if needed.
   Tailwind CSS classes are primarily used for styling. */
</style>
