<template>
    <div class="game-bets">
        <div class="row">
            <div class="col-lg-12">
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['match'],

        data: function() {
            return  {
                shared: store.state,
                bets: null,
            }
        },
        methods: {
            fetch() {
                this.$http.get('/api/user/bets')
                    .then(function (response) {
                        console.log(response.data);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },
        mounted() {
            this.fetch();
        },
    }
</script>
