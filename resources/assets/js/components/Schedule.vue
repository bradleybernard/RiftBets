<template>

<div v-if="fetched == true">
    Schedule component
    <div v-for="(item, key) in stats">
        {{ key }}
        <div v-for="match in item" style="color: white">
            {{ match.name }}
        </div>
        <hr>
    </div>
</div>
</template>

<script>
export default {
    mounted() {
        this.getSchedule();
    },

    data() {
        return {
            shared: store.state,
            stats: [],
            fetched: false
        };
    },

    methods: {
        getSchedule: function() {
            this.stats = this.$http.get('http://riftbets.dev/api/schedule?league=')
              .then(response => {
                this.stats = response.data;
                this.fetched = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
}
</script>
