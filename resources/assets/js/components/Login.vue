<template>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div v-if="!shared.user.loggedIn" class="btn btn-success btn-block"  @click="login">Login with Facebook</div>
                <div v-else class="alert alert-success">
                    <p>You are already logged in!</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {shared: store.state};
    },
    methods: {
        login(e) {
            var auth = window.open('/auth/facebook', '_blank', 'width=300,height=700');
        },
    },
}
</script>
