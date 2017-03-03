<template>
    <div class="game-nav">
        <div class="row" v-if="fetched == true" style="font-size: 25px; line-height: 75px;" >
            <div class="col-xs-4 ">
                <img :src="matchData.resources.one.logo_url" style="width: 75px; height: 75px">
            </div>
            <div class="col-xs-4">
                <span class="hidden-xs">{{ matchData.name }}</span>
                {{ matchData.score_one }} - {{ matchData.score_two }}
            </div>
            <div class="col-xs-4">
                <img :src="matchData.resources.two.logo_url" style="width: 75px; height: 75px">
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['match'],

    mounted() {
        this.getMatchData(this.match);
    },

    data() {
        return {
            shared: store.state,
            matchData: [],
            fetched : false,
        };
    },

    methods: {
        getMatchData: function(id) {
            this.stats = this.$http.get('http://riftbets.dev/api/match?match_id=' + id).then(response => {
                this.matchData = response.data;
                this.fetched = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
}
</script>
