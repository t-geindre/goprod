<script>
var jQuery = require('jquery');

module.exports = {
    props: ['targetElement', 'data', 'displayField'],
    data: function() {
        return {
            items: [],
            target: null,
            list: null,
            query: '',
            currentItem: 0,
            focused: false,
            throttle: false
        }
    },
    mounted: function() {
        this.target = jQuery(this.targetElement);
        this.list = jQuery('.dropdown-menu', this.$el);

        this.list.css({
            top: this.target.outerHeight()
        });

        this.target
            .focus(() => { this.focus(); })
            .blur(() => { this.blur(); })
            .keyup((event) => { this.typing(event); });

        this.typing({});
    },
    methods: {
        focus: function() {
            this.focused = true;
            this.target.select();
        },
        blur: function() {
            this.focused = false;
            setTimeout(() => {
                if (!this.focused) {
                    this.items = [];
                }
            }, 100);
        },
        typing: function(event) {
            if (event.which == 38) {
                event.preventDefault();
                return this.up();
            }
            if (event.which == 40) {
                event.preventDefault();
                return this.down();
            }
            if (event.which == 13) {
                if (this.query.length == 0) {
                    this.select(undefined);
                }
                if (this.items[this.currentItem]) {
                    this.select(this.items[this.currentItem]);
                }
            }
            this.query = this.target.val();
        },
        current: function(item) {
            this.currentItem = item;
        },
        up: function() {
            this.currentItem--;
            if (this.currentItem < 0) {
                this.currentItem = this.items.length - 1;
            }
        },
        down: function() {
            this.currentItem++;
            if (this.currentItem > this.items.length - 1) {
                this.currentItem = 0;
            }
        },
        select: function(item) {
            if (this.query.length == 0) {
                item = undefined;
            }
            this.target.blur();
            this.$emit('select', item);
        }
    },
    watch: {
        query: function() {
            if (this.throttle) {
                clearTimeout(this.throttle);
            }
            if (this.query.length < 2) {
                this.items = [];
                return;
            }
            this.throttle = setTimeout(() => {
                this.data(this.query).then((response) => {
                    this.items = response.data;
                    this.up();
                    this.down();
                });
            }, 200);
        }
    }
};
</script>

<template>
    <div class="dropdown">
        <ul class="dropdown-menu" v-bind:class="{ hide: items.length == 0 }">
            <li
                v-for="(item, index) in items"
                v-on:mouseover="current(index)"
                v-bind:class="{ active: currentItem == index }"
                v-on:click.prevent="select(item)"
            >
                <a href="#">{{ item[displayField] }}</a>
            </li>
        </ul>
    </div>
</template>

<style scoped>
    .dropdown-menu {
        display: block;
    }
    .hide {
        display: none;
    }
</style>

