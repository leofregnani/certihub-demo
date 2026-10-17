<script setup>
import CrudPage from '../components/CrudPage.vue'

const columns = [
  { key: 'name', label: 'Name' },
  { key: 'email', label: 'E-mail' },
  { key: 'document', label: 'Document' },
  { key: 'phone', label: 'Phone' },
]

const fields = [
  { key: 'name', label: 'Full name', required: true },
  { key: 'email', label: 'E-mail', type: 'email' },
  { key: 'document', label: 'Document / ID' },
  { key: 'phone', label: 'Phone' },
]
</script>

<template>
  <CrudPage title="Participants" noun="participant" url="/participants" :columns="columns" :fields="fields" />
</template>
