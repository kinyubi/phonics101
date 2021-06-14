<?

    require dirname(__DIR__) . '/autoload.php';

    use App\ReadXYZ\Models\Session;

    $words = ['fat,cat,hat,sat,mat,pat,bat,rat,vat',
          'cap,gap,lap,map,rap,sap,tap,zap,nap',
          'bag,hag,jag,lag,nag,rag,sag,tag,wag',
          'ban,can,fan,lan,man,pan,ran,tan,van',
          'pap,dad,bab,dab,bad,pad,bad,pad,dad',
          'bit,fit,hit,kit,mitt,pit,sit,wit,zit',
          'big,dig,fig,jig,pig,rig,wig,zig,gig',
          'dip,hip,jip,lip,nip,pip,rip,sip,zip',
          'cot,dot,got,hot,jot,lot,not,pot,rot',
          'bog,cog,dog,fog,hog,jog,log,hop,top',
          'fob,fop,gob,God,hop,job,lob,mob,mod',
          'mop,nod,pod,pop,rob,rod,sob,sod,top',
          'but,cut,gut,hut,jut,mutt,nut,putt,rut',
          'bug,dug,hug,lug,jug,mug,pug,rug,tug',
          'bud,dub,dud,pub,pug,pup,dub,bud,dud',
          'bet,get,jet,let,met,net,pet,set,wet'];

    Session::sessionContinue();

    if (isset($_SESSION['TicTacToe'])) {
      $word_list = $_SESSION['TicTacToe'];
    } else {
        $list_count = count($words);
        $idx = rand(0, $list_count - 1);
        $word_list = explode(',', $words[$idx]);
    }
    
    echo json_encode($word_list);

?>
