const connect = require('../libs/soap');

module.exports = async function purchase({ document, phone, price }) {
    const client = await connect();
    return new Promise((res, rej) => {
        client.purchase({ document, phone, price }, function (err, result) {
            if (err) {
                rej(err);
            } else {
                const response = result.return.item.reduce((acc, item) => {
                    acc[item["key"]["$value"]] = item?.value?.$value;
                    return acc;
                }, {})
                res(response);
            }
        });
    })
}