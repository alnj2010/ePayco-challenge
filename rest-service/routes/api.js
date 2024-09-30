var express = require('express');
var router = express.Router();
const { body, validationResult, query } = require('express-validator');
const url = `http://soap-service-container:8000/api/soap/wsdl`;
const soap = require('soap');

async function connect() {

  return new Promise((res, rej) => {
    soap.createClient(url, function (err, client) {
      if (err) {
        rej(err);
      } else {
        res(client);
      }
    });
  })
}

async function register({ document, email, phone, name }) {
  const client = await connect(url);
  return new Promise((res, rej) => {
    client.register({ document, email, phone, name }, function (err, result) {
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

async function charge({ document, phone, amount }) {
  const client = await connect(url);
  return new Promise((res, rej) => {
    client.charge({ document, phone, amount }, function (err, result) {
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

async function checkBalance({ document, phone }) {
  const client = await connect(url);
  return new Promise((res, rej) => {
    client.check_balance({ document, phone }, function (err, result) {
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

async function purchase({ document, phone, price }) {
  const client = await connect(url);
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

async function confirm({ verify }) {
  const client = await connect(url);
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



router.post(
  '/register',
  body('document').notEmpty().isString(),
  body('email').notEmpty().isString().isEmail(),
  body('phone').notEmpty().isString(),
  body('name').notEmpty().isString(),
  async function (req, res, next) {
    const result = validationResult(req);

    if (!result.isEmpty()) {
      return res.json({
        success: false,
        cod_error: '400',
        message_error: JSON.stringify(result.array()),
        data: null
      }).status(400);
    }


    try {

      const result = await register(req.body)
      res.json(result);

    } catch (error) {
      console.log(error);
      res.json({
        success: false,
        cod_error: '500',
        message_error: error.message,
        data: null
      });
    }
  });


router.put('/charge',
  body('document').notEmpty().isString(),
  body('phone').notEmpty().isString(),
  body('amount').notEmpty().isDecimal(),
  async function (req, res, next) {

    const result = validationResult(req);
    if (!result.isEmpty()) {
      return res.json({
        success: false,
        cod_error: '400',
        message_error: JSON.stringify(result.array()),
        data: null
      }).status(400);
    }
    try {
      const result = await charge(req.body)
      res.json(result);

    } catch (error) {
      res.json({
        success: false,
        cod_error: '500',
        message_error: 'Internal error',
        data: null
      }).status(500);
    }
  });


router.get('/check-balance',
  body('document').notEmpty().isString(),
  body('phone').notEmpty().isString(),
  async function (req, res, next) {
    const result = validationResult(req);
    if (!result.isEmpty()) {
      return res.json({
        success: false,
        cod_error: '400',
        message_error: JSON.stringify(result.array()),
        data: null
      }).status(400);
    }

    try {
      console.log(req.body);
      const result = await checkBalance(req.body)
      res.json(result);

    } catch (error) {
      res.json({
        success: false,
        cod_error: '500',
        message_error: 'Internal error',
        data: null
      }).status(500);
    }
  });

router.post('/purchase',
  body('document').notEmpty().isString(),
  body('phone').notEmpty().isString(),
  body('price').notEmpty().isDecimal(),
  async function (req, res, next) {
    const result = validationResult(req);
    if (!result.isEmpty()) {
      return res.json({
        success: false,
        cod_error: '400',
        message_error: JSON.stringify(result.array()),
        data: null
      }).status(400);
    }

    try {
      const result = await purchase(req.body)
      res.json(result);
    } catch (error) {
      res.json({
        success: false,
        cod_error: '500',
        message_error: 'Internal error',
        data: null
      }).status(500);
    }
  });

router.get('/confirm-purchase',
  query('verify').notEmpty().isString(),
  async function (req, res, next) {
    const result = validationResult(req);
    if (!result.isEmpty()) {
      return res.json({
        success: false,
        cod_error: '400',
        message_error: JSON.stringify(result.array()),
        data: null
      }).status(400);
    }

    try {
      const result = await confirm(req.query)
      res.json(result);
    } catch (error) {
      res.json({
        success: false,
        cod_error: '500',
        message_error: 'Internal error',
        data: null
      }).status(500);
    }

  });


module.exports = router;
