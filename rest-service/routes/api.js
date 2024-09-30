const { body, validationResult, query } = require('express-validator');
var express = require('express');
var router = express.Router();
const register = require("../services/register");
const charge = require("../services/charge");
const checkBalance = require("../services/checkBalance");
const purchase = require("../services/purchase");
const confirm = require("../services/confirm");


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
