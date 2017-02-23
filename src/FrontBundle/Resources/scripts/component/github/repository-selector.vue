<script>
var GithubClient = require('../../lib/github-client.js');
var UserStore    = require('../../store/user.js');

module.exports = {
    props: ['owner', 'repo', 'disabled'],
    data: function() {
        return {
            organizations: [],
            organization: {},
            repository: ''
        }
    },
    computed: {
        user: function() {
            return UserStore.state.user;
        }
    },
    mounted: function() {
        GithubClient.getOrganizations().then((response)=> {
            this.organizations = response.data;
            this.updateDefaults();
        });
    },
    methods: {
        selectRepository: function(repository) {
            if (repository) {
                this.organization = repository.owner;
                this.repository = repository;
                this.$emit('repo', this.repository.name);
                this.$emit('owner', this.organization.login);
                return;
            }
            this.$emit('repo', '');
        },
        searchRepositories: function(q) {
            return new Promise((resolve, reject) => {
                return GithubClient.searchRepositories({
                    q: q + (this.organization.login ? ' user:'+this.organization.login : '')
                })
                .then((response) => {
                    resolve(response.data.items);
                })
                .catch(reject);
            });
        },
        selectOrganization: function(organization) {
            if (organization != this.organization) {
                this.organization = organization;
                this.repository = '';
                this.$emit('owner', this.organization.login);
                this.$emit('repo', '');
            }
        },
        updateDefaults: function() {
            if (this.owner) {
                this.organizations.forEach((organization) => {
                    if (organization.login == this.owner) {
                        this.organization = organization;
                    }
                });
            }
            if (this.repo) {
                this.repository = { name: this.repo };
            }
        }
    },
    watch: {
        repo: function() {
            this.updateDefaults();
        },
        owner: function() {
            this.updateDefaults();
        }
    },
    components: {
        'typeahead-repositories': require('../typeahead/repositories.vue')
    }
};
</script>

<template>
    <div class="input-group">
        <div class="input-group-btn disabled">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" v-bind:disabled="disabled">
                <template v-if="organization.login">
                    <img v-bind:src="organization.avatar_url" />
                    {{ organization.login }}
                </template>
                <template v-else>
                    <span class="glyphicon glyphicon-asterisk"></span>
                </template>
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="#" v-on:click.prevent="selectOrganization(user)">
                        <img v-bind:src="user.avatar_url" />
                        {{ user.login }}
                    </a>
                </li>
                <li v-for="organization in organizations">
                    <a href="#" v-on:click.prevent="selectOrganization(organization)">
                        <img v-bind:src="organization.avatar_url" />
                        {{ organization.login }}
                    </a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                    <a href="#" class="all" v-on:click.prevent="selectOrganization({})">
                        <span class="glyphicon glyphicon-asterisk"></span>
                        All organizations
                    </a>
                </li>
            </ul>
        </div>
        <typeahead-repositories
            display-field="name" class="form-control" min-length="2"
            v-on:select="selectRepository" v-on:clear="selectRepository(false)"
            placeholder="repository" v-bind:disabled="disabled" v-bind:default-value="repository.name"
            v-bind:source="searchRepositories"
        >
        </typeahead-repositories>
    </div>
</template>

<style type="text/css" scoped>
    .dropdown-menu>li>a {
        padding-left: 10px;
    }
    img {
        border-radius: 5px;
    }
    a img {
        width: 30px;
        margin-right: 5px;
    }
    button img {
        height: 18px;
        border-radius: 3px;
    }
</style>
