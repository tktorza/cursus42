section .text
global _ft_strcat
extern _ft_strlen
_ft_strcat:
    mov r15, rdi
    cmp rsi, 0 ;verif chaine non nulle
    je return
    mov rdi, rsi ;nombre octet à copier
    call _ft_strlen
    mov r14, rax
    inc r14
    mov rdi, r15 ;remise des registres de base
    call _ft_strlen
    cmp rax, 0
    je secondloop    
    inc rax
    mov al, 0
    cld
    repne scasb
    dec rdi
    
secondloop:
    dec rdi
    mov rcx, r14
    repne movsb
    mov rdi, 0

return:
    mov rax, r15 
    ret