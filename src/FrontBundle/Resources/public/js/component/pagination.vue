<script>
module.exports = {
    props: ['pages', 'page'],
    computed: {
        displayPages: function() {
            var start = Math.max(1, this.page - 5);
            var end = Math.min(this.page + 5, this.pages);
            var pages = [];

            for (var i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        }
    },
    methods: {
        goToPage: function(page) {
            this.$emit('page', page);
        }
    }
};
</script>

<template>
    <ul class="pagination" v-if="pages > 1">
        <li v-if="page > 1">
            <a href="#" aria-label="Previous" v-on:click.prevent="goToPage(page+-1)">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <li v-for="id in displayPages" v-bind:class="{ active: id == page }">
            <a href="#" v-on:click.prevent="goToPage(id)">{{ id }}</a>
        </li>
        <li v-if="page < pages">
            <a href="#" aria-label="Next" v-on:click.prevent="goToPage(page+1)">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</template>
