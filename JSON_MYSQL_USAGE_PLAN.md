
#JSON/MYSQL
##Division of Labor

###abc_gametypes
- JSON only
- implementing class: GameTypesJson
- key structures:
    - GameType stdClass 
        - gameTypeId (primary)
        - gameTitle
        - thumbNailUrl
        - cssClass
        - belongsOnTab
        - url (if empty get value from abc_lessons.game)
        - active
    - map[] -  [ [gameTypeId => GameType_stdClass] ]
    - universalGames[] - [ gameType_stdClass]
- required functionalities:
    - get($gameTypeId) 
    - given a list of abc_lessons.game objects
- deprecates abc_gametypes
- related to:
    - abc_lessons.game stdClass
        - gameTypeId
        - resource
        - url

###abc_tabtypes
- JSON only
- implementing class: TabTypesJson
- key structures:
    - TabType stdClass
        - tabTypeId
        - tabDisplayAs
        - script
        - imageFile
        - aliases[]
- deprecates 
    - abc_tabtypes table, 
    - Data\TabTypesData, 
    - Lessons\TabTypes,
    - POPO\TabType

###abc_groups
- JSON only
- implementing class GroupsJson
- key structures
    - Groups stdClass
        - groupCode (primary)
        - groupName
        - groupDisplayAs (need to deprecate. same as groupName)
        - active
        - ordinal
    - map[] - [ [groupCode => Groups_stdClass ]]
    - nameMap[]  [ [groupName|groupCode => groupCode ]]
- deprecates abc_groups table, Data\GroupData, Lessons\Groups

###abc_keychain
- JSON only
- implements class KeyChainJson
- key structures
    - KeyChain stdClass
        - groupCode (primary)
        - filename
        - friendlyName
    - map [ [groupCode => KeyChain_stdClass] ]
-deprecates abc_keychain table


