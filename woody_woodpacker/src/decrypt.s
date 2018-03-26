section .text
global _decrypt_true
extern _ft_putnbr
extern _ft_putchar
;RDI,   RSI,   RDX, RCX, R8, R9, XMM0–7
;data, offset, size, key, index

_decrypt_true:
    ;enter 16, 0
	;push rdx
	;push rcx
	;push rsi
    mov r15, 1 ;sign = 1
    ;mov r8, rcx
	lea rdi, [rdi + rsi]
	;push rdi
    mov r13, rdx
    ;mov r12, rax
    ;push rdx
    ;push rsi
	mov rax, rsi
    jmp loop

negative:
    shr r11, 1 ;index >> 1
    jmp secondy

firstbigcond:;si x=0
    cmp rax, rdx
    je loopping
    xor rax, rax
    add BYTE [rdi], bl;
    sub BYTE [rdi], r11b
    
    ;add rdi, rbx; --> data[k] += bit de data[k] de l'index
    ;sub rdi, r11
    jmp loopping

loop:
    ;push rdi
    ;rdi, r15=sign * r8=index??? --> on retrouve juste les val de index et de sign
    xor r10, r10
    mov r10, r8 ;--> index = 7 - index
    ;determine 2^index
    mov rbx, 1
    cmp r10, 0
    je following

;not sure of follow
shlindex:
    shl rbx, 1;rbx << --> jusqu'a bon bit à comparer
    sub r10, 1
    cmp r10, 0
    jne shlindex
    
following:
    ;determine y --> rdi & index

    mov rax, [rdi]
    and rax, rbx
    mov r11, rbx
    cmp r15, -1; if sign == -1
    je negative
    shl r11, 1 ;index << 1
    ;rbx = x, rax = y
    ;r11=x2, rdx = y2 

secondy:
    mov rdx, [rdi]
    and rdx, r11
    cmp rax, 0
    je firstbigcond

secondbigcond:;si x=1
    cmp rax, rdx
    je loopping
;  xor rax, rax
    sub BYTE [rdi], bl
    add BYTE [rdi], r11b
    ;jmp loopping

loopping:
    ;;faire data[k] =  reverse_bit_index(data[k], index * sign);
;r12 ???
    mov rax, r12
    sub rax, rsi
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
    
increment:
    ;mov rax, 141
    ;mov [rdi], rax
    ;pop rdi
    ;pb d'incrementation ici ;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
    ;pop rdi
    ;;;;;;;;;;;;;;;;;;;;;;;;;;;A VERIFIERRRRR
    ;lea rdi, [rdi + 1]
    inc rdi
    
    ;mov rax, rdi
    ;leave
    ;ret

    add r12, 1
    cmp r12, r13 
    jne loop
    jmp return

sixinf:
    add r8, 1 ;inc r8
    jmp increment 

positive:
    cmp r8, 6
    jl sixinf
    mov r15, -1
    jmp increment

indexsup:
    sub r8, 1
    jmp increment

bigelse:
    cmp r15, 0
    jg positive
    cmp r8, 1
    jg indexsup
    mov r15, 1
    jmp increment

return:
    ;pop rbp
    ;mov rsp, rbp
    ;leave
    ret
    ;jmp 0x11111111
