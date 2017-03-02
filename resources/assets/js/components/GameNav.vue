<template>
    <div class="col-lg-12">
        <div class="alert">
            <div v-if="fetched == true">
                <div style="width:81.5em; height:11em; border:1px solid #000;  vertical-align: middle;">
                    <h3 class="row" style="display: inline-block; font-size: 32pt;">
                        <div class="col-md-2">
                            <img :src="matchData.resources.one.logo_url" style="max-width: 70%;"></img>
                        </div>
                        <div class="col-md-3" style="padding-top: .5em">
                            <span style="">{{ matchData.name }}</span>
                            <br>
                            <span style="padding-left: .5em;">{{ matchData.score_one }}-{{ matchData.score_two }}</span>
                        </div>
                        <div class="col-md-2">
                            <img :src="matchData.resources.two.logo_url" style="max-width: 70%"></img>
                        </div>
                        <div class="col-md-5" style="padding-top: 1em">
                            <span>{{ matchData.league[0].name }}</span>
                            <br>
                            <span style="padding-left: .5em;">{{ matchData.league[0].scheduled_time.substring(10,16) }}</span>
                        </div>
                    </h3>
                </div>
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
            this.stats = this.$http.get('http://riftbets.dev/api/match?match_id=' + id)
              .then(response => {
                this.matchData = response.data;
                this.fetched = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
}
</script>
