<script>
module.exports = {};
</script>

<template>
    <p class="loading-spinner"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span></p>
</template>

<style type="text/css" scoped>
    .glyphicon-refresh-animate {
        -animation: spin .7s infinite linear;
        -webkit-animation: spin2 .7s infinite linear;
    }

    @-webkit-keyframes spin2 {
        from { -webkit-transform: rotate(0deg);}
        to { -webkit-transform: rotate(360deg);}
    }

    @keyframes spin {
        from { transform: scale(1) rotate(0deg);}
        to { transform: scale(1) rotate(360deg);}
    }

    .loading-spinner {
        padding: 20px;
        text-align:center;
    }

    .loading-spinner.big {
        font-size:60px;
    }

    .loading-spinner.medium {
        font-size:30px;
    }
</style>
