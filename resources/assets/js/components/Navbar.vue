<template>
    <ul class="nav navbar-nav navbar-right">
        <template v-if="!shared.user.loggedIn">
            <li><a href="/login" @click.prevent="login">Login</a></li>
            <li><a href="/register" @click.prevent="login">Register</a></li>
        </template>
        <template v-else>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                    {{ shared.user.name }} <span class="caret"></span>
                </a>

                <ul class="dropdown-menu" role="menu">
                    <li><a href="/logout" @click.prevent="logout">Logout</a></li>
                </ul>
            </li>
        </template>
    </ul>
</template>

<script>
export default {
    data() {
        return {shared: store.state};
    },
    methods: {
        logout(e) {
            this.shared.user.loggedIn = false;
            this.$cookie.delete('laravel_session', {domain: 'riftbets.dev'});
            this.$cookie.delete('jwt', {domain: 'riftbets.dev'});
        },
        login(e) {
            var auth = window.open('/auth/facebook', '_blank', 'width=300,height=700');
        },
    }
}
</script>
