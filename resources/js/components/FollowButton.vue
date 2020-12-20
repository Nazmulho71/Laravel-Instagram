<template>
    <div>
        <button class="btn btn-info btn-sm ml-3" @click="followUser" v-text="buttonText">Follow</button>
    </div>
</template>

<script>
    export default {
        props: ['userId', 'follows'],

        data: function () {
            return {
                status: this.follows,
            }
        },

        methods: {
            followUser() {
                axios.post('/follows/' + this.userId)
                    .then(response => {
                        this.status = ! this.status;
                        console.log(response.data);
                    })
                    .catch(e => {
                        if (e.response.status == 401) {
                            window.location = '/login';
                        };
                    });
            }
        },

        computed: {
            buttonText() {
                return (this.status) ? 'Unfollow' : 'Follow';
            }
        },

        mounted() {
            console.log('Component mounted!')
        }
    }
</script>
