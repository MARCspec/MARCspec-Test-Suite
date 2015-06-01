<?php
require 'vendor/autoload.php';

use Seld\JsonLint\JsonParser;

$char = function($postionOrRange)
{
    return "/".$postionOrRange;
};

$index = function($postionOrRange)
{
    return "[".$postionOrRange."]";
};

$indicator = function($ind)
{
    return "_".$ind;
};

$subfield = function($subfieldTagOrRange)
{
    return "$".$subfieldTagOrRange;
};

$comparison = function($comp)
{
    return "\\".$comp;
};

function get_and_decode($path,$callback)
{
    $json = json_decode(file_get_contents($path));
    if(!$callback) return $json->{'tests'};
    array_walk(
    $json->{'tests'},
    function(&$v, $k) use ($callback)
    {
        $v->{'data'} = $callback($v->{'data'});
    }
    );
    return $json->{'tests'};
}

function make_json($json,$valid,$desc,$name)
{
    $validtext = ($valid) ? 'valid' : 'invalid';
    $obj['description'] = $desc;
    $obj['schema'] = ['type'=>'string'];
    $obj['tests'] = [];
    foreach($json as $jt)
    {
        $_t['description'] = $validtext.' wild combination: '.$jt->{'description'};
        $_t['data'] = '...'.$jt->{'data'};
        $_t['valid'] = $valid;
        $obj['tests'][] = $_t;
    }
    $parser = new JsonParser();
    $enc = json_encode($obj,JSON_PRETTY_PRINT);
    if(is_null($e = $parser->lint($enc)))
    {
        write_json($name,$validtext,$enc);
    }
    else
    {
        print $e->getDetails();
    }
}

function write_json($name,$validtext,$content)
{
    $handle = fopen($validtext.'/wildCombination_'.$name.'.json', "w+");
    fwrite($handle,$content);
    fclose($handle);
}

function combine($_t1,$_t2)
{
    $combination = [];
    
    array_walk(
        $_t1,
        function($t1,$k) use ($_t2,&$combination)
        {
            foreach($_t2 as $t2)
            {
                $_t = new stdClass();
                $_t->{'data'} = $t1->{'data'}.$t2->{'data'};
                $_t->{'description'} =  $t1->{'description'}.' && '.$t2->{'description'};
                $_t->{'valid'} = $t2->{'valid'};
                $combination[] = $_t;
            }
        }
    );
    
    return $combination;
}

$_json['validComparisonString'] = get_and_decode('valid/validComparisonString.json',$comparison);
$_json['validIndicators'] = get_and_decode('valid/validIndicators.json',$indicator);
$_json['validChar'] = get_and_decode('valid/validPositionOrRange.json',$char);
$_json['validIndex'] = get_and_decode('valid/validPositionOrRange.json',$index);
$_json['validSubfieldRange'] = get_and_decode('valid/validSubfieldRange.json',$subfield);
$_json['validSubfieldTag'] = get_and_decode('valid/validSubfieldTag.json',$subfield);
$_json['validSubSpec'] = get_and_decode('valid/validSubSpec.json',false);

$_json['invalidComparisonString'] = get_and_decode('invalid/invalidComparisonString.json',$comparison);
$_json['invalidIndicators'] = get_and_decode('invalid/invalidIndicators.json',$indicator);
$_json['invalidChar'] = get_and_decode('invalid/invalidPositionOrRange.json',$char);
$_json['invalidIndex'] = get_and_decode('invalid/invalidPositionOrRange.json',$index);
$_json['invalidSubfieldRange'] = get_and_decode('invalid/invalidSubfieldRange.json',$subfield);
$_json['invalidSubfieldTag'] = get_and_decode('invalid/invalidSubfieldTag.json',$subfield);



/**
* Valid combinations
*/ 

make_json($_json['validIndex'],true,"wild combination of valid field tag and index",'validIndex');
make_json($_json['validChar'],true,"wild combination of valid field tag and charspec",'validChar');
make_json($_json['validIndicators'],true,"wild combination of valid field tag and indicator",'validIndicators');
make_json($_json['validSubfieldTag'],true,"wild combination of valid field tag and subfield tag",'validSubfieldTag');
make_json($_json['validSubfieldRange'],true,"wild combination of valid field tag and subfield range",'validSubfieldRange');
make_json($_json['validSubSpec'],true,"wild combination of valid field tag and subspec",'validSubSpec');

$combineIndexIndicator = combine($_json['validIndex'],$_json['validIndicators']);
make_json($combineIndexIndicator,true,"wild combination of valid field tag, index and indicators",'validIndexIndicator');

$combineIndexIndicatorSubSpec = combine($combineIndexIndicator,$_json['validSubSpec']);
make_json($combineIndexIndicatorSubSpec,true,"wild combination of valid field tag, index, indicators and subspec",'validIndexIndicatorSubSpec');

$combineIndexChar = combine($_json['validIndex'],$_json['validChar']);
make_json($combineIndexChar,true,"wild combination of valid field tag, index and charspec",'validIndexChar');

$combineIndexCharSubSpec = combine($combineIndexChar,$_json['validSubSpec']);
make_json($combineIndexCharSubSpec,true,"wild combination of valid field tag, index, charspec and subspec",'validIndexCharSubSpec');

$combineSubfieldTagSubSpec = combine($_json['validSubfieldTag'],$_json['validSubSpec']);
make_json($combineSubfieldTagSubSpec,true,"wild combination of valid field tag, subfield tag and subspec",'validSubfieldTagSubSpec');

$combineSubfieldTagRange = combine($_json['validSubfieldTag'],$_json['validSubfieldRange']);
make_json($combineSubfieldTagRange,true,"wild combination of valid field tag, subfield tag and subfield range",'validSubfieldTagRange');

$combineSubfieldRangeSubSpec = combine($_json['validSubfieldRange'],$_json['validSubSpec']);
make_json($combineSubfieldRangeSubSpec,true,"wild combination of valid field tag, subfield range and subspec",'validSubfieldRangeSubSpec');

$combineSubfieldTagTag = combine($_json['validSubfieldTag'],$_json['validSubfieldTag']);
make_json($combineSubfieldTagTag,true,"wild combination of valid field tag and subfield tags",'validSubfieldTagTag');

$combineSubfieldSubSpecTag = combine($_json['validSubSpec'],$_json['validSubfieldTag']);
make_json($combineSubfieldSubSpecTag,true,"wild combination of valid field tag with subspec and subfield tag",'validSubfieldSubSpecTag');

$combineSubfieldRangeRange = combine($_json['validSubfieldRange'],$_json['validSubfieldRange']);
make_json($combineSubfieldRangeRange,true,"wild combination of valid field tag and subfield ranges",'validSubfieldRangeRange');

$combineSubSpecSubSpec = combine($_json['validSubSpec'],$_json['validSubSpec']);
make_json($combineSubSpecSubSpec,true,"wild combination of valid field tag and subspecs",'validSubSpecSubSpec');

/*
$combineSubSpecTag = combine($_json['validSubSpec'],$_json['validSubfieldTag']);
$combineSubSpecTagSubSpec = combine($combineSubSpecTag,$_json['validSubSpec']);
make_json($combineSubSpecTagSubSpec,true,"wild combination of valid field tag with subspec and subfield tag with subspec",'validSubSpecSubfieldTagSubSpec');
+/


/*
* Invalid combinations
*/

make_json($_json['invalidIndex'],false,"wild combination of valid field tag and invalid index",'invalidIndex');
make_json($_json['invalidChar'],false,"wild combination of valid field tag and invalid charspec",'invalidChar');
make_json($_json['invalidIndicators'],false,"wild combination of valid field tag and invalid indicator",'invalidIndicators');
make_json($_json['invalidSubfieldTag'],false,"wild combination of valid field tag and invalid subfield tag",'invalidSubfieldTag');
make_json($_json['invalidSubfieldRange'],false,"wild combination of valid field tag and invalid subfield range",'invalidSubfieldRange');

$invalidCombineCharIndicator = combine($_json['validChar'],$_json['validIndicators']);
make_json($invalidCombineCharIndicator,false,"wild combination of valid field tag and charspec",'invalidCharIndicator');





