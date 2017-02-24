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
        mounted() {
            this.$http.get('/api/match?match_id=74430344-e4fd-43e3-b618-e5b10c9e34e2')
                .then(function (response) {
                    console.log(response.data);
                })
                .catch(function (error) {
                    console.log(error);
                });
        },
    }
</script>
