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
        this.$http.get('http://riftbets.dev/api/schedule?league=')
          .then(function (response) {
            console.log(response);
          })
          .catch(function (error) {
            console.log(error);
          });
    },

    data() {
        return {shared: store.state};
    },

    methods: {

    },
}
</script>
