<script>
module.exports = {props:['class']};
</script>

<template>
    <p class="loading-spinner"></p>
</template>

<style type="text/css" scoped>
    @keyframes spinner {
        to {transform: rotate(360deg);}
    }
    .loading-spinner {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        border: 2px solid #ccc;
        border-top-color: #07d;
        animation: spinner .4s linear infinite;
        margin: 50px auto;
    }

    .loading-spinner.big {
        width: 60px;
        height: 60px;
    }

    .loading-spinner.medium {
        width: 30px;
        height: 30px;
    }

    .loading-spinner.inline {
        display: inline-block;
        padding:0;
        margin:0;
        vertical-align: text-top;
    }
</style>
