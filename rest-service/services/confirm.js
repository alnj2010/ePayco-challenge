const connect = require('../libs/soap');

module.exports = async function confirm({ verify }) {
    const client = await connect();
    return new Promise((res, rej) => {
        client.confirm({ verify_token: verify }, function (err, result) {
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