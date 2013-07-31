var m_strUpperCase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
var m_strLowerCase = "abcdefghijklmnopqrstuvwxyz";
var m_strNumber = "0123456789";
var m_strCharacters = "!@#$%^&*?_~"

function checkPassword(strPassword)
{
    // Reset combination count
    var nScore = 0;

    // Password length
    // -- Less than 4 characters
    if (strPassword.length < 5)
    {
        nScore += 5;
    }
    // -- 5 to 7 characters
    else if (strPassword.length > 4 && strPassword.length < 8)
    {
        nScore += 10;
    }
    // -- 8 or more
    else if (strPassword.length > 7)
    {
        nScore += 25;
    }

    // Letters
    var nUpperCount = countContain(strPassword, m_strUpperCase);
    var nLowerCount = countContain(strPassword, m_strLowerCase);
    var nLowerUpperCount = nUpperCount + nLowerCount;
    // -- Letters are all lower case
    if (nUpperCount == 0 && nLowerCount != 0) 
    { 
        nScore += 10; 
    }
    // -- Letters are upper case and lower case
    else if (nUpperCount != 0 && nLowerCount != 0) 
    { 
        nScore += 20; 
    }

    // Numbers
    var nNumberCount = countContain(strPassword, m_strNumber);
    // -- 1 number
    if (nNumberCount == 1)
    {
        nScore += 10;
    }
    // -- 3 or more numbers
    if (nNumberCount >= 3)
    {
        nScore += 20;
    }

    // Characters
    var nCharacterCount = countContain(strPassword, m_strCharacters);
    // -- 1 character
    if (nCharacterCount == 1)
    {
        nScore += 10;
    }   
    // -- More than 1 character
    if (nCharacterCount > 1)
    {
        nScore += 25;
    }

    // Bonus
    // -- Letters and numbers
    if (nNumberCount != 0 && nLowerUpperCount != 0)
    {
        nScore += 2;
    }
    // -- Letters, numbers, and characters
    if (nNumberCount != 0 && nLowerUpperCount != 0 && nCharacterCount != 0)
    {
        nScore += 3;
    }
    // -- Mixed case letters, numbers, and characters
    if (nNumberCount != 0 && nUpperCount != 0 && nLowerCount != 0 && nCharacterCount != 0)
    {
        nScore += 5;
    }


    return nScore;
}

// Runs password through check and then updates GUI 


function runPassword(strPassword, strFieldID) 
{
    // Check password
    var nScore = checkPassword(strPassword);


     // Get controls
        var ctlBar = document.getElementById(strFieldID + "_bar"); 
        var ctlText = document.getElementById(strFieldID + "_text");
        if (!ctlBar || !ctlText)
            return;

        // Set new width
        ctlBar.style.width = (nScore*1.25>100)?100:nScore*1.25 + "%";

    // Color and text
    // -- Very Secure
    /*if (nScore >= 90)
    {
        var strText = "Very Secure";
        var strColor = "#0ca908";
    }
    // -- Secure
    else if (nScore >= 80)
    {
        var strText = "Secure";
        vstrColor = "#7ff67c";
    }
    // -- Very Strong
    else 
    */
    if (nScore >= 80)
    {
        var strText = "Very Strong";
        var strColor = "#008000";
    }
    // -- Strong
    else if (nScore >= 60)
    {
        var strText = "Strong";
        var strColor = "#006000";
    }
    // -- Average
    else if (nScore >= 40)
    {
        var strText = "Average";
        var strColor = "#e3cb00";
    }
    // -- Weak
    else if (nScore >= 20)
    {
        var strText = "Weak";
        var strColor = "#Fe3d1a";
    }
    // -- Very Weak
    else
    {
        var strText = "Very Weak";
        var strColor = "#e71a1a";
    }

    if(strPassword.length == 0)
    {
    ctlBar.style.backgroundColor = "";
    ctlText.innerHTML =  "";
    }
else
    {
    ctlBar.style.backgroundColor = strColor;
    ctlText.innerHTML =  strText;
}
}

// Checks a string for a list of characters
function countContain(strPassword, strCheck)
{ 
    // Declare variables
    var nCount = 0;

    for (i = 0; i < strPassword.length; i++) 
    {
        if (strCheck.indexOf(strPassword.charAt(i)) > -1) 
        { 
                nCount++;
        } 
    } 

    return nCount; 
} 