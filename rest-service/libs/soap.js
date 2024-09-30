const soap = require('soap');

module.exports = async function connect() {
    return new Promise((res, rej) => {
        soap.createClient(process.env.SOAP_APP, function (err, client) {
            if (err) {
                rej(err);
            } else {
                res(client);
            }
        });
    })
}