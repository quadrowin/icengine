var Helper_Render_Smarty;

// TODO: Debugging mode vs stop-on-error mode - runtime flag.
// TODO: Handle || (or) characters and backslashes.
// TODO: Add more modifiers.

(function()
{               // Using a closure to keep global namespace clean.
    if (Helper_Render_Smarty == null)
    {
        Helper_Render_Smarty = new Object();
    }
    if (Helper_Render_Smarty.evalEx == null)
    {
        Helper_Render_Smarty.evalEx = function(src) {return eval(src);};
    }

    var UNDEFINED;
    if (Array.prototype.pop == null) // IE 5.x fix 
    {
        Array.prototype.pop = function() {
            if (this.length === 0) {return UNDEFINED;}
            return this[--this.length];
        };
    }
    if (Array.prototype.push == null) // IE 5.x fix 
    {
        Array.prototype.push = function() {
            for (var i = 0; i < arguments.length; ++i) {this[this.length] = arguments[i];}
            return this.length;
        };
    }

    Helper_Render_Smarty.parseTemplate = function(tmplContent, optTmplName, optEtc)
    {
        if (optEtc == null)
            optEtc = Helper_Render_Smarty.parseTemplate_etc;
        var funcSrc = parse(tmplContent, optTmplName, optEtc);
	
        var func = Helper_Render_Smarty.evalEx(funcSrc, optTmplName, 1);

        if (func != null)
            return new optEtc.Template(optTmplName, tmplContent, funcSrc, func, optEtc);
        return null;
    };
    
    try
    {
        String.prototype.process = function(context, optFlags)
        {
            var template = Helper_Render_Smarty.parseTemplate(this, null);
            if (template != null)
                return template.process(context, optFlags);
            return this;
        };
    }
    catch (e)
    {
    	// Swallow exception, such as when String.prototype is sealed.
    };
    
    Helper_Render_Smarty.parseTemplate_etc = {};            // Exposed for extensibility.
    Helper_Render_Smarty.parseTemplate_etc.statementTag = "foreachelse|foreach|if|elseif|else|assign|macro";
    Helper_Render_Smarty.parseTemplate_etc.statementDef = { // Lookup table for statement tags.
        "if"     : {
			delta:  1,
			prefix: "if (",
//			prefixFunc : function(stmtParts, state, tmplName, etc) {
//				stmtParts.splice (0, 1);
//				var condition = stmtParts.join (' ');
//				return condition;
//			},
			suffix: ") {",
			paramMin: 1
		},
        "else"   : {
			delta:  0,
			prefix: "} else {"
		},
        "elseif" : {
			delta:  0,
			prefix: "} else if (",
			suffix: ") {",
			paramDefault: "true"
		},
        "/if"    : {delta: -1, prefix: "}"},
        "foreach"    : {delta:  1, paramMin: 2, 
                     prefixFunc : function(stmtParts, state, tmplName, etc) {
                    	var token = stmtParts.join (' ').replace (' = ', '=', 'g');
                    	
                    	var parts = token.split (' ');
                    	var foreach = parts [0];
                    	var from = parts [1];
                    	
                		if (from.indexOf ('=') > 0)
                		{
                			fromVar = from.split ('=')[1];
                			from = from.split ('=')[0];
                		}
                		else
                		{
                			throw new etc.ParseError(tmplName, state.line, "bad for loop statement: " + stmtParts.join(' '));
                		}
                		
                		fromVar = fromVar.substr (1, fromVar.length - 1);
                		
                		if (!fromVar)
                		{
                			throw new etc.ParseError(tmplName, state.line, "bad for loop statement: " + stmtParts.join(' '));
                		}
                		
                		var item = parts [2];
                		
                		if (item.indexOf ('=') > 0)
                		{
                			iterVar = item.split ('=')[1];
                			item = item.split ('=')[0];
                		}
                		else
                		{
                			throw new etc.ParseError (tmplName, state.line, "bad for loop statement: " + stmtParts.join(' '));
                		}
                		
                    	if (iterVar.substr (0,1) == '"' || iterVar.substr (0,1) == "'")
                        {
                        	iterVar = iterVar.substr (1, iterVar.length - 2);
                        }
                    	
                        if (from != "from" || item != 'item')
                        {
                            throw new etc.ParseError (tmplName, state.line, "bad for loop statement: " + stmtParts.join(' '));
                        }
                        
                        var keyVar = '__KEY__';
                        var nameVar = '__NAME__';

                        var sts = [ 'key', 'name' ];
                        
                        for (var i = 0, l = sts.length; i < l; i++)
                        {
	                        for (var j in sts)
	                        {
	                        	if (parts [3 + i])
	                        	{
	                        		st = parts [3 + i].split ('=')[0];
	                        		if (st)
	                        		{
	                        			if (st == sts [j])
	                        			{
	                        				tmp = parts [3 + i].split ('=')[1];
	                        				if (tmp.substr (0,1) == '"' || tmp.substr (0,1) == "'")
	                                        {
	                        					tmp = tmp.substr (1, tmp.length - 2);
	                                        }
	                        				eval (st + 'Var="' + tmp + '";');
	                        			}
	                        		}
	                        	}
	                        }
                        }

                        var listVar = "__LIST__" + iterVar;
                        
                        return [ "var ", listVar, " = ", fromVar, ";",
                             "var c=0;",
                             "for(var i in ", listVar,"){c++;};",
                             // Fix from Ross Shaull for hash looping, make sure that we have an array of loop lengths to treat like a stack.
                             "var __LENGTH_STACK__;",
                             "if (typeof(__LENGTH_STACK__) == 'undefined' || !__LENGTH_STACK__.length) __LENGTH_STACK__ = new Array();", 
                             "__LENGTH_STACK__[__LENGTH_STACK__.length] = 0;", // Push a new for-loop onto the stack of loop lengths.
                             "if ((", listVar, ") != null) { ",
                             "var ", iterVar, "_ct = 0;",       // iterVar_ct variable, added by B. Bittman     
                             "for (var ", iterVar, "_index in ", listVar, ") { ",
                             keyVar, "=", iterVar, "_index;",
                             "if(!smarty){var smarty={section:{},foreach:{}};};",
                             "if(!smarty.foreach.", nameVar, "){smarty.foreach.", nameVar, "={last:0,first:1,iteration:1};};",
                             iterVar, "_ct++;",
                             "smarty.foreach.", nameVar, ".iteration=", iterVar, "_ct;", 
                             "if(", iterVar, "_ct", ">0){smarty.foreach.",nameVar, ".first=0;};",
                             "if(", iterVar, "_ct==c){smarty.foreach.",nameVar, ".last=1;};",
                             "if (typeof(", listVar, "[", iterVar, "_index]) == 'function') {continue;}", // IE 5.x fix from Igor Poteryaev.
                             "__LENGTH_STACK__[__LENGTH_STACK__.length - 1]++;",
                             "var ", iterVar, " = ", listVar, "[", iterVar, "_index];" ].join("");
                     }},
        "foreachelse" : {
        	delta:  0,
        	prefix: "} } if (__LENGTH_STACK__[__LENGTH_STACK__.length - 1] == 0) { if (",
        	suffix: ") {",
        	paramDefault: "true"
        },
        "/foreach"    : {
        	delta: -1,
        	prefix: "} }; delete __LENGTH_STACK__[__LENGTH_STACK__.length - 1];"
        }, // Remove the just-finished for-loop from the stack of loop lengths.
        "assign"     : {
        	delta:  0,
			prefixFunc : function (stmtParts, state, tmplName, etc)
            {
				var var_index = 1;
				var var_name = '';
				
				if (stmtParts [1].indexOf ('var=') < 0)
				{
					var_index = 3;
					var_name = stmtParts [3];
				}
				else
				{
					var_name = stmtParts [1].substr (4);
				}
				
				if (
					var_name.substr (0, 1) == "'" ||
					var_name.substr (0, 1) == '"'
				)
				{
					var_name = var_name.substr (1, var_name.length - 2);
				}
				
				var value = '';
				
				if (stmtParts [var_index + 1].substr (0, 6) == 'value=')
				{
					value = 
						stmtParts [var_index + 1].substr (6) + " " +
						stmtParts.slice (var_index + 2).join (" ");
						
				}
				else
				{
					value = stmtParts.slice (var_index + 2).join (" ");
				}
				
				if (value.substr (0, 1) == "'")
				{
					value = '"' + value.substr (1, value.length - 2) + '"';
				}
				
                return [ "View_Render.assign('", var_name, "', ", value, ");"].join ('');
            }
        },
        "macro"   : {
        	delta:  1, 
            prefixFunc : function(stmtParts, state, tmplName, etc)
            {
            	var macroName = stmtParts[1].split('(')[0];
                return [ "var ", macroName, " = function", 
                                   stmtParts.slice(1).join(' ').substring(macroName.length),
                                   "{ var _OUT_arr = []; var _OUT = { write: function(m) { if (m) _OUT_arr.push(m); } }; " ].join('');
            }
        }, 
        "/macro"  : {
        	delta: -1,
        	prefix: " return _OUT_arr.join(''); };"
        }
    };
    
    Helper_Render_Smarty.parseTemplate_etc.modifierDef = {
		"eat"        : function(v)    { return ""; },
		"escape"     : function(s)    { return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"); },
		"capitalize" : function(s)    { return String(s).toUpperCase(); },
		"default"    : function(s, d) {	return s ? s : d; }
    };
    
    Helper_Render_Smarty.parseTemplate_etc.modifierDef.h = Helper_Render_Smarty.parseTemplate_etc.modifierDef.escape;

    Helper_Render_Smarty.parseTemplate_etc.Template = function (tmplName, tmplContent, funcSrc, func, etc)
    {
        this.process = function(context, flags)
        {
            if (context == null)
            {
                context = {};
            }
            
            if (context._MODIFIERS == null)
            {
                context._MODIFIERS = {};
            }
            
            if (context.defined == null)
            {
                context.defined = function(str) {return (context[str] != undefined);};
            }
            
            for (var k in etc.modifierDef)
            {
                if (context._MODIFIERS[k] == null)
                {
                    context._MODIFIERS[k] = etc.modifierDef[k];
                }
            }
            
            if (flags == null)
            {
                flags = {};
            }
            var resultArr = [];
            var resultOut = {
            	write: function(m) {
            		resultArr.push(m);
            	}
            };
            try
            {
                func(resultOut, context, flags);
            }
            catch (e)
            {
                if (flags.throwExceptions == true)
                {
                    throw e;
                }
                var result = new String (resultArr.join ("") + "[ERROR: " + e.toString () + (e.message ? '; ' + e.message : '') + "]");
                result["exception"] = e;
                return result;
            };
            return resultArr.join ("");
        };
        this.name       = tmplName;
        this.source     = tmplContent; 
        this.sourceFunc = funcSrc;
        this.toString   = function() {return "Helper_Render_Smarty.Template [" + tmplName + "]";};
    };
    
    Helper_Render_Smarty.parseTemplate_etc.ParseError = function(name, line, message)
    {
        this.name    = name;
        this.line    = line;
        this.message = message;
    };
    
    Helper_Render_Smarty.parseTemplate_etc.ParseError.prototype.toString = function()
    { 
        return ("Helper_Render_Smarty template ParseError in " + this.name + ": line " + this.line + ", " + this.message);
    };
    
    var parse = function(body, tmplName, etc)
    {
        body = cleanWhiteSpace (body);
        var funcText = [ "var Helper_Render_Smarty_Template_TEMP = function(_OUT, _CONTEXT, _FLAGS) { with (_CONTEXT) {" ];
        var state    = {
        	stack: [],
        	line: 1
        };                              // TODO: Fix line number counting.
        var endStmtPrev = -1;
        while (endStmtPrev + 1 < body.length) {
            var begStmt = endStmtPrev;
            // Scan until we find some statement markup.
            begStmt = body.indexOf ("{", begStmt + 1);
            while (begStmt >= 0)
            {
                var endStmt = body.indexOf ('}', begStmt + 1);
                var stmt = body.substring(begStmt, endStmt);
                var blockrx = stmt.match(/^\{(cdata|minify|eval)/); // From B. Bittman, minify/eval/cdata implementation.
                if (blockrx)
                {
                    var blockType = blockrx [1]; 
                    var blockMarkerBeg = begStmt + blockType.length + 1;
                    var blockMarkerEnd = body.indexOf ('}', blockMarkerBeg);
                    if (blockMarkerEnd >= 0)
                    {
                        var blockMarker;
                        if (blockMarkerEnd - blockMarkerBeg <= 0)
                        {
                            blockMarker = "{/" + blockType + "}";
                        }
                        else
                        {
                            blockMarker = body.substring (blockMarkerBeg + 1, blockMarkerEnd);
                        }                        
                        
                        var blockEnd = body.indexOf(blockMarker, blockMarkerEnd + 1);
                        if (blockEnd >= 0)
                        {                            
                            emitSectionText (body.substring (endStmtPrev + 1, begStmt), funcText);
                            
                            var blockText = body.substring (blockMarkerEnd + 1, blockEnd);
                            
                            if (blockType == 'cdata')
                            {
                                emitText(blockText, funcText);
                            }
                            else if (blockType == 'minify')
                            {
                                emitText(scrubWhiteSpace(blockText), funcText);
                            }
                            else if (blockType == 'eval')
                            {
                                if (blockText != null && blockText.length > 0) // eval should not execute until process().
                                {
                                    funcText.push('_OUT.write( (function() { ' + blockText + ' })() );');
                                }
                            }
                            begStmt = endStmtPrev = blockEnd + blockMarker.length - 1;
                        }
                    }                        
                } else if (
                	body.charAt(begStmt - 1) != '$' &&		// Not an expression or backslashed,
                    body.charAt(begStmt - 1) != '\\'		// so check if it is a statement tag.
                )
                {
                    var offset = (body.charAt(begStmt + 1) == '/' ? 2 : 1); // Close tags offset of 2 skips '/'.                                                   // 10 is larger than maximum statement tag length.
                    if (body.substring(begStmt + offset, begStmt + 10 + offset).search(Helper_Render_Smarty.parseTemplate_etc.statementTag) == 0)
                    {
                        break;                                              // Found a match.
                    }
                }
                begStmt = body.indexOf("{", begStmt + 1);
            }
            if (begStmt < 0)                              // In "a{for}c", begStmt will be 1.
            {
                break;
            }
            var endStmt = body.indexOf("}", begStmt + 1); // In "a{for}c", endStmt will be 5.
            if (endStmt < 0)
            {
                break;
            }
            emitSectionText(body.substring(endStmtPrev + 1, begStmt), funcText);
            emitStatement(body.substring(begStmt, endStmt + 1), state, funcText, tmplName, etc);
            endStmtPrev = endStmt;
        }
        emitSectionText(body.substring(endStmtPrev + 1), funcText);
        if (state.stack.length != 0)
        {
            throw new etc.ParseError (tmplName, state.line, "unclosed, unmatched statement(s): " + state.stack.join(","));
        }
        funcText.push("}}; Helper_Render_Smarty_Template_TEMP");
        return funcText.join("");
    };
    
    var emitStatement = function(stmtStr, state, funcText, tmplName, etc) {
        var parts = stmtStr.slice(1, -1).split(' ');
        var stmt = etc.statementDef[parts[0]]; // Here, parts[0] == for/if/else/...
        if (stmt == null)		// Not a real statement.
        {
            emitSectionText (stmtStr, funcText);
            return;
        }
        if (stmt.delta < 0)
        {
            if (state.stack.length <= 0)
                throw new etc.ParseError(tmplName, state.line, "close tag does not match any previous statement: " + stmtStr);
            state.stack.pop();
        } 
        if (stmt.delta > 0)
            state.stack.push(stmtStr);
        
        if (stmt.paramMin != null &&
            stmt.paramMin >= parts.length)
            throw new etc.ParseError(tmplName, state.line, "statement needs more parameters: " + stmtStr);
        if (stmt.prefixFunc != null)
            funcText.push(stmt.prefixFunc(parts, state, tmplName, etc));
        else 
            funcText.push(stmt.prefix);
        if (stmt.suffix != null) {
            if (parts.length <= 1) {
                if (stmt.paramDefault != null)
                    funcText.push(stmt.paramDefault);
            } else {
                for (var i = 1; i < parts.length; i++) {
                    if (i > 1)
                        funcText.push(' ');
                    funcText.push(parts[i]);
                }
            }
            funcText.push(stmt.suffix);
        }
    };

    var emitSectionText = function(text, funcText) {
        if (text.length <= 0)
            return;
        var nlPrefix = 0;               // Index to first non-newline in prefix.
        var nlSuffix = text.length - 1; // Index to first non-space/tab in suffix.
        while (nlPrefix < text.length && (text.charAt(nlPrefix) == '\n'))
            nlPrefix++;
        while (nlSuffix >= 0 && (text.charAt(nlSuffix) == ' ' || text.charAt(nlSuffix) == '\t'))
            nlSuffix--;
        if (nlSuffix < nlPrefix)
            nlSuffix = nlPrefix;
        if (nlPrefix > 0) {
            funcText.push('if (_FLAGS.keepWhitespace == true) _OUT.write("');
            var s = text.substring(0, nlPrefix).replace('\n', '\\n'); // A macro IE fix from BJessen.
            if (s.charAt(s.length - 1) == '\n')
			{
            	s = s.substring(0, s.length - 1);
			}
            funcText.push(s);
            funcText.push('");');
        }
        var lines = text.substring(nlPrefix, nlSuffix + 1).split('\n');
        for (var i = 0; i < lines.length; i++) {
            emitSectionTextLine(lines[i], funcText);
            if (i < lines.length - 1)
			{
                funcText.push('_OUT.write("\\n");\n');
			}
        }
        if (nlSuffix + 1 < text.length) {
            funcText.push('if (_FLAGS.keepWhitespace == true) _OUT.write("');
            var s = text.substring(nlSuffix + 1).replace('\n', '\\n');
            if (s.charAt(s.length - 1) == '\n')
			{
            	s = s.substring(0, s.length - 1);
			}
            funcText.push(s);
            funcText.push('");');
        }
    };
    
    var emitSectionTextLine = function(line, funcText) {
        var endMarkPrev = '}';
        var endExprPrev = -1;
        while (endExprPrev + endMarkPrev.length < line.length) {
            var begMark = "{$", endMark = "}";
            var begExpr = line.indexOf(begMark, endExprPrev + endMarkPrev.length); // In "a${b}c", begExpr == 1
            if (begExpr < 0)
			{
                break;
			}
            if (line.charAt(begExpr + 2) == '%')
			{
                begMark = "{$%";
                endMark = "%}";
            }
            var endExpr = line.indexOf(endMark, begExpr + begMark.length);         // In "a${b}c", endExpr == 4;
            if (endExpr < 0)
			{
                break;
			}
            emitText(line.substring(endExprPrev + endMarkPrev.length, begExpr), funcText);                
            // Example: exprs == 'firstName|default:"John Doe"|capitalize'.split('|')
            var exprArr = line.substring(begExpr + begMark.length, endExpr).replace(/\|\|/g, "#@@#").split('|');
			for (var k in exprArr)
			{
                if (exprArr[k].replace) // IE 5.x fix from Igor Poteryaev.
				{
                    exprArr[k] = exprArr[k].replace(/#@@#/g, '||');
				}
            }
            funcText.push('_OUT.write(');
            emitExpression(exprArr, exprArr.length - 1, funcText); 
            funcText.push(');');
            endExprPrev = endExpr;
            endMarkPrev = endMark;
        }
        emitText(line.substring(endExprPrev + endMarkPrev.length), funcText); 
    };
    
    var emitText = function(text, funcText) {
        if (text == null || text.length <= 0)
		{
            return;
		}
        text = text.replace(/\\/g, '\\\\');
        text = text.replace(/\n/g, '\\n');
        text = text.replace(/"/g,  '\\"');
        funcText.push('_OUT.write("');
        funcText.push(text);
        funcText.push('");');
    };
    
    var emitExpression = function(exprArr, index, funcText) {
        // Ex: foo|a:x|b:y1,y2|c:z1,z2 is emitted as c(b(a(foo,x),y1,y2),z1,z2)
        var expr = exprArr[index]; // Ex: exprArr == [firstName,capitalize,default:"John Doe"]
        if (index <= 0) {          // Ex: expr    == 'default:"John Doe"'
			funcText.push(expr);
            return;
        }
        var parts = expr.split(':');
        funcText.push('_MODIFIERS["');
        funcText.push(parts[0]); // The parts[0] is a modifier function name, like capitalize.
        funcText.push('"](');
        emitExpression(exprArr, index - 1, funcText);
        if (parts.length > 1) {
            funcText.push(',');
            funcText.push(parts[1]);
        }
        funcText.push(')');
    };

    var cleanWhiteSpace = function(result) {
        result = result.replace(/\t/g,   "    ");
        result = result.replace(/\r\n/g, "\n");
        result = result.replace(/\r/g,   "\n");
        result = result.replace(/^(\s*\S*(\s+\S+)*)\s*$/, '$1'); // Right trim by Igor Poteryaev.
        return result;
    };

    var scrubWhiteSpace = function(result) {
        result = result.replace(/^\s+/g,   "");
        result = result.replace(/\s+$/g,   "");
        result = result.replace(/\s+/g,   " ");
        result = result.replace(/^(\s*\S*(\s+\S+)*)\s*$/, '$1'); // Right trim by Igor Poteryaev.
        return result;
    };

    // The DOM helper functions depend on DOM/DHTML, so they only work in a browser.
    // However, these are not considered core to the engine.
    //
    Helper_Render_Smarty.parseDOMTemplate = function(elementId, optDocument, optEtc) {
        var content = elementId.replace(/&lt;/g, "<").replace(/&gt;/g, ">");
        return Helper_Render_Smarty.parseTemplate(content, elementId, optEtc);
    };

    Helper_Render_Smarty.processDOMTemplate = function(elementId, context, optFlags, optDocument, optEtc) {
        return Helper_Render_Smarty.parseDOMTemplate(elementId, optDocument, optEtc).process(context, optFlags);
    };
}) ();
