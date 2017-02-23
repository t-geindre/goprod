<script>
module.exports = {
    props: ['displayField', 'source', 'minLength', 'placeholder', 'disabled', 'defaultValue'],
    data: function() {
        return {
            items: [],
            query: '',
            throttle: false,
            currentItem: 0,
            throttle: false,
            hasFocus: false,
            lastSelected : null,
            sourceFunc: false
        }
    },
    mounted: function() {
        this.updateDefaultValue();
    },
    methods: {
        current: function(index) {
            this.currentItem = index;
        },
        up: function() {
            this.currentItem--;
        },
        down: function() {
            this.currentItem++;
        },
        search: function() {
            if (this.throttle) {
                clearTimeout(this.throttle);
            }
            if (this.query.length >= this.minLength) {
                var source = this.source;
                if (this.sourceFunc) {
                    source = this.sourceFunc;
                }
                this.throttle = setTimeout(() => {
                    source(this.query).then((items) => {
                        this.items = items;
                    });
                }, 200);
                return;
            }
            this.clear();
        },
        select: function() {
            var item = this.items[this.currentItem];
            if (item) {
                this.query = item[this.displayField];
                this.clear();
                this.$emit('select', item);
                this.lastSelected = this.query;
            }
            if (this.query.length == 0) {
                this.$emit('clear');
            }
        },
        render: function (item) {
            return item.name;
        },
        clear: function() {
            this.items = [];
        },
        blur: function() {
            this.hasFocus = false;
            setTimeout(() => {
                if (!this.hasFocus) {
                    this.clear();
                }
            }, 100);
        },
        focus: function(event) {
            event.target.select();
            this.hasFocus = true;
            this.search();
        },
        checkCurrent: function() {
            if (this.currentItem > this.items.length - 1) {
                this.currentItem = 0;
                return;
            }
            if (this.currentItem < 0) {
                this.currentItem = this.items.length - 1;
            }
        },
        updateDefaultValue: function() {
            var defaultValue = this.defaultValue;
            if (!defaultValue) {
                defaultValue = '';
            }
            this.lastSelected = defaultValue;
            this.query = defaultValue;
        }
    },
    watch: {
        currentItem: function() {
            this.checkCurrent();
        },
        items: function() {
            this.checkCurrent();
        },
        query: function() {
            if (this.lastSelected == this.query) {
                return;
            }
            this.search();
        },
        defaultValue: function() {
            this.updateDefaultValue();
        }
    },
    components: {
        'item': require('./item/item.vue')
    }
};
</script>

<template>
    <span>
        <input
            type="text"
            autocomplete="off"
            v-on:keydown.prevent.up="up"
            v-on:keydown.prevent.down="down"
            v-model="query"
            v-on:blur="blur"
            v-on:focus="focus"
            v-on:keydown.enter="select"
            v-bind:placeholder="placeholder"
            v-bind:disabled="disabled"
        />
        <ul class="dropdown-menu" v-if="items.length > 0">
            <li
                v-for="(item, index) in items"
                v-on:mouseover="current(index)"
                v-bind:class="{ active: currentItem == index }"
                v-on:click.prevent="select"
            >
                <item v-bind:item="item" v-bind:display-field="displayField"></item>
            </li>
        </ul>
    </span>
</template>

<style scoped>
    .dropdown-menu {
        display: block;
    }
    input {
        border:0;
        width: 100%;
        padding: 0;
        margin: 0;
    }
    input:focus {
        border:0;
        outline: 0;
    }
    li.active a {
        background-color:#3e95e0;
    }
    span > input[disabled] {
        cursor: not-allowed;
        background-color: transparent;
    }
</style>

