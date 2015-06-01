# MARCspec Test Suite

This repository contains a set of JSON objects that developers of MARCspec parser libraries can use to test their implementations.

## Overview

Tests are divided into valid and invalid. Thus there are two directories valid/ and invalid/.

Each directory contains multiple ```.json``` files and each file contains one root JSON object with multiple test objects.

The filenames might give you a hint on which tests are covered. The files starting with ```wildCombination_``` are (wild) combinations of the test data in the files starting with ```valid``` or ```invalid```.

## JSON structure

Each root JSON object contains an object with the description, a schema to validate against and some tests. Each test is an object itself with a description, the test data and a validation statement (true or false).

Here is an example of teh content of a ```.json``` file

```json
{
    "description": "field tags are strings and match pattern",
    "schema": {
      "type": "string",
      "pattern": "^([.a-z0-9]{3,3}|[.A-Z0-9]{3,3})$"
    },
    "tests": [
        {
            "description": "all wildcards",
            "data": "...",
            "valid": true
        },
        {
            "description": "two wildcards left with digit",
            "data": "..0",
            "valid": true
        },
        {
            "description": "one wildcard left with two digits",
            "data": ".00",
            "valid": true
        }
    ]
}
```

## About Tests

Not every file contains complete MARCspec references. Only the files starting with ```wildCombination_``` and the files ```*FieldTag.json``` does.

The other files:

- *ComparisonString.json
- *Indicators.json
- *PositionOrRange.json
- *SubfieldRange.json
- *SubfieldTag.json
- *SubSpec.json

contain either valid or invalid fragments of MARCspec references. This makes it easy to test distinct functionalities (e.g. a ComparisonString paser).