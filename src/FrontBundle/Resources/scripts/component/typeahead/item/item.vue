<script>
module.exports ={
    props: ['item', 'displayField']
}
</script>

<template>
    <a href="#">{{ item[displayField] }}</a>
</template>
