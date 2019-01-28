const buildUrl = function(baseUrl, params){
    const url = new URL(location.origin + baseUrl);
    Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
    return url;
};
const app = new Vue({
    el: '#app',
    data: {
        collection: 1,
        key: "",
        name: "",
        items: [],
        itemsMap: {}
    },
    methods: {
        search: function(){
            const app = this;
            fetch(buildUrl(`/search/${this.collection}`, {key: this.key, name: this.name})).then(function(response) {
                return response.json();
            }).then(function(items) {
                app.items = items;
                app.itemsMap = {};
                items.forEach((item, index) => {
                    app.itemsMap[item.key] = index;
                });
            });
        },
        load: function(event){
            const app = this, key = event.target.value;
            if(typeof app.itemsMap[key] === "undefined") return;
            fetch(`/links/${this.collection}/${key}`).then(function(response) {
                return response.json();
            }).then(function(links) {
                const items = app.items.slice();
                items[app.itemsMap[key]].links = links;
                app.items = items;
            });
        }
    }
});