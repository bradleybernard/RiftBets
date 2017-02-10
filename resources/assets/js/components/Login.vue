<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Login</div>

                    <div class="panel-body">
                        Login thru Facebook to start betting now!
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        mounted() {
            Echo.channel('match.14cecce8-f45b-42cb-8478-45c357a2d099')
                .listen('GameCompleted', (e) => {
                    console.log(e);
                });
        }
    }
</script>
