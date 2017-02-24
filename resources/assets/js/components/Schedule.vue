<template>

<div>
    <template>
    Schedule component
    </template>
</div>
</template>

<script>
export default {
    mounted() {
        console.log('Schedule component mounted.');
        
    },

    data() {
        return {shared: store.state};
    },

    methods: {

    },
}
</script>
