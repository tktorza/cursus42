section .text
global _decrypt_true
extern _ft_putnbr
extern _ft_putchar
;RDI,   RSI,   RDX, RCX, R8, R9, XMM0–7
;data, offset, size, key, index

_decrypt_true:
    enter 16, 0
	;push rdx
	;push rcx
	;push rsi
    mov r15, 1 ;sign = 1
    ;mov r8, rcx
	lea rdi, [rdi + rsi]
;	push rdi
push rdx
push rsi
	mov rax, rsi
    jmp loop

negative:
    mov r11, rbx
    shr r11, 1 ;index >> 1
    jmp secondy

firstbigcond:;si x=0
    cmp r12, r13
    je loopping
    add rdi, rbx; --> data[k] += bit de datak de l'index
    sub rdi, r11
    jmp loopping

loop:
    ;rdi, r15=sign * r8=index??? --> on retrouve juste les val de index et de sign
    xor r10, r10
    ;mov r10, 7
    ;sub r10, r8 ;--> index = 7 - index
    ;determine 2^index
    mov rbx, 1
    cmp r8, 0
    je following

;not sure of follow
shlindex:
    shl rbx, 1
    dec r8
    cmp r8, 0
    jne shlindex
    
following:
    ;determine y --> rdi & index
    mov r12, rdi
    and r12, rbx
    cmp r15, -1; if sign == -1
    je negative
    mov r11, rbx
    shl r11, 1 ;index << 1
    ;rbx = x, r12 = y
    ;r11=x2, r13 = y2 

secondy:
    mov r13, rdi
    and r13, r11
    cmp r12, 0
    jne firstbigcond

secondbigcond:;si x=1
    cmp r12, r13
    je loopping
    sub rdi, rbx; --> data[k] -= bit de datak de l'index
    add rdi, r11
    ;jmp loopping

loopping:
    ;;faire data[k] =  reverse_bit_index(data[k], index * sign);


    xor r10, r10 ;maybe can be push off
    pop r10
    push r10
    sub rax, r10
    ;rax nbr à diviser
    mov rbx, rcx
    xor rdx, rdx
    div rbx
    cmp rdx, 0
    jne bigelse
    mov rbx, 7
    mov rax, rcx
    xor rdx, rdx
    div rbx
    mov r8, rdx ;index = val_key % 7
    ;inc rdi et index ---------------AFAIRE
    inc rdi
    inc r8
    pop rdx
    pop rdx
    cmp r8, rdx 
    jne loop
    return

sixinf:
    add r8, 1 ;inc r8
    jmp loop 

positive:
    cmp r8, 6
    jl sixinf
    mov r15, -1
    jmp loop

indexsup:
    sub r8, 1

bigelse:
    cmp r15, 0
    jg positive
    cmp r8, 1
    jg indexsup
    mov r15, 1
    jmp loop

return:
;    pop rcx
;   pop rdx
    pop rbp
    mov rsp, rbp
    ret
