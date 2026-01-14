<template>
  <div class="mx-auto">
    <ExpenseList />
  </div>
</template>

<script>
import ExpenseList from '../components/ExpenseList.vue'

export default {
  name: 'HomeView',
  components: { ExpenseList }
}
</script>
