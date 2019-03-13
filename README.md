# json gateway ISPConfig

This is a json gateway to ISPConfigs Soap API. It is not and I think it will never be a one 2 one implementation. We just add all the functions like we need. :-) Sorry for that.

## supported remote api

- user login
- create invoice

## How to start

```bash
docker run \
    -e username=$username \
    -e password=$password \
    -e location=$location \
    -e billing=$billing \
    -e uri=$uri \
    -e id=$id \
    -e vat=$vat \
    -e default_invoice_template=$default_invoice_template \
    -e default_invoice_email_template=$default_invoice_email_template \
    -e payment_terms=$payment_terms \
    -e allow_self_signed=$allow_self_signed \
    -p 8777:8888 \
    avhost/ispconfig-jsonrpc 
```
