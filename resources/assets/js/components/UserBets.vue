<template>
    <div class="col-lg-12">
        <div class="alert">
            <p>Test</p>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return  {
                detail: null,
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
