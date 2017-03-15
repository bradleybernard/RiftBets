<template>
    <div class="leader-board">
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-1">
                Rank
            </div>
            <div class="col-md-3">
                Name
            </div>
            <div class="col-md-8">
                Score
            </div>
        </div>
        <div class="row" v-for="user in LeaderBoard.users" style="padding-bottom: 20px;">
            <div class="col-md-1">
                {{ user.rank }}
            </div>
            <div class="col-md-3">
                {{ user.name }}
            </div>
            <div class="col-md-8">
                {{ user.stat }}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['leaderboard'],

    mounted() {
        this.getLeaderBoard(this.leaderboard);
    },

    data() {
        return {
            LeaderBoard: null,
        };
    },

    methods: {
        getLeaderBoard: function(id) {
            this.$http.get('/api/leaderboards/signedin?leaderboard=' + id).then(response => {
                this.LeaderBoard = response.data;
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
}
</script>